<?php

namespace RGies\JiraListWidgetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JiraRestApi\Issue\IssueService;
use JiraRestApi\Issue;
use JiraRestApi\Configuration\ArrayConfiguration;
use JiraRestApi\Issue\TimeTracking;
use JiraRestApi\JiraException;


/**
 * Widget controller.
 *
 * @Route("/jira_list_widget")
 */
class DefaultController extends Controller
{
    /**
     * Collect needed widget data.
     *
     * @Route("/collect-data/", name="JiraListWidgetBundle-collect-data")
     * @Method("POST")
     * @return Response
     */
    public function collectDataAjaxAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
        {
            return new Response('No valid request', Response::HTTP_FORBIDDEN);
        }

        // Allow php to handle parallel request.
        // Please remove if you need to write something to the session.
        session_write_close();

        $widgetId       = $request->get('id');
        $widgetType     = $request->get('type');
        $updateInterval = $request->get('updateInterval');
        $size           = $request->get('size');

        // Get data from cache
        $cache = $this->get('CacheService');
        if ($cacheValue = $cache->getValue('JiraListWidgetBundle', $widgetId, null, $updateInterval)) {
            return new Response($cacheValue, Response::HTTP_OK);
        }

        $jiraLogin = $this->get('JiraCoreService')->getLoginCredentials();
        $widgetConfig = $this->get('WidgetService')->getResolvedWidgetConfig($widgetType, $widgetId);

        $response = array();
        $colSpacer = str_repeat('&nbsp;',5);
        $maxLines = ($size=='1x2'||$size=='2x2') ? 15 : 5;

        $jql = $widgetConfig->getJqlQuery();

        try {
            $issueService = new IssueService($jiraLogin);
            $issues = $issueService->search(
                $jql,
                0,
                $maxLines,
                ['key','summary','assignee','created','status','resolutiondate','aggregatetimespent']
            );
        } catch (JiraException $e) {
            $response['warning'] = wordwrap($e->getMessage(), 38, '<br/>');
            return new Response(json_encode($response), Response::HTTP_OK);
        }

        $value = 'NO ITEMS FOUND';
        if ($issues) {
            $value = '<table>';

            $z = 0;
            foreach ($issues->getIssues() as $issue) {
                if ($z++ >= $maxLines) break;

                $value = $value . '<tr style="white-space: nowrap;"><td><i class="fa fa-circle"></i> '
                    . $this->_createLink($issue->key,$issue->fields->summary)
                    . $colSpacer . '</td>';

                if ($issue->fields->assignee) {
                    $assignee = $this->_getShortName($issue->fields->assignee->displayName);
                } else {
                    $assignee = 'Unassigned';
                }

                $timeSpend = '0h';
                if ($issue->fields->aggregatetimespent) {
                    if ($issue->fields->aggregatetimespent / 3600 > 600) {
                        $timeSpend = round($issue->fields->aggregatetimespent / 3600 / 8, 1) . 'd';
                    } else {
                        $timeSpend = round($issue->fields->aggregatetimespent / 3600, 1) . 'h';
                    }
                }

                switch ($widgetConfig->getExtendedInfo())
                {
                    case 'status_age':
                        $status = 'Unknown';
                        if ($issue->fields->status) {
                            $status = $this->_getShortSummery($issue->fields->status->name, 15);
                        }

                        $value .= '<td><i title="State" class="fa fa-tag"></i> <i>' . $status . '</i></td>'
                            . '<td>' . $colSpacer . '<i title="Issue Age" class="fa fa-coffee"></i> <i>'
                            . $this->_formatIssueAge($issue) . '</i></td>';

                        if ($size == '2x1' or $size == '2x2') {
                            $value .= '<td><i>' . $colSpacer . $this->_getShortSummery($issue->fields->summary, 30)
                                . '</i></td>';
                        }
                        break;

                    case 'age_invest':
                        $value .= '<td><i title="Issue age" class="fa fa-coffee"></i> <i>' . $this->_formatIssueAge($issue) . '</i></td>'
                            . '<td>' . $colSpacer . '<i title="Time spend" class="ion ion-android-stopwatch"></i> <i>'
                            . $timeSpend . '</i></td>';

                        if ($size == '2x1' or $size == '2x2') {
                            $value .= '<td><i>' . $colSpacer . $this->_getShortSummery($issue->fields->summary, 30)
                                . '</i></td>';
                        }
                        break;

                    case 'assignee_invest':
                        $value .= '<td><i title="Assigned to" class="fa fa-user"></i> <i>' . $assignee . '</i></td>'
                            . '<td>&nbsp;&nbsp;<i title="Time spend" class="ion ion-android-stopwatch"></i> <i>'
                            . $timeSpend . '</i></td>';

                        if ($size == '2x1' or $size == '2x2') {
                            $value .= '<td><i>' . $colSpacer . $this->_getShortSummery($issue->fields->summary, 40)
                                . '</i></td>';
                        }
                        break;

                    case 'summery':
                    default:
                        $value .= '<td><i><span title="' . str_replace('"', '&quot;', $issue->fields->summary)
                            . '">' . $this->_getShortSummery($issue->fields->summary, 15)
                            . '</span></i></td>';
                        break;
                }


                $value .= '</tr>';
            }
            $value .= '</table>';
        }

        $response['link'] = $jiraLogin->getJiraHost() . '/issues/?jql=' . urlencode($jql);
        $response['total'] = $issues->getTotal();
        $response['value'] = $value;

        // Cache response data
        $cache->setValue('JiraListWidgetBundle', $widgetId, json_encode($response));

        return new Response(json_encode($response), Response::HTTP_OK);
    }

    /**
     * Format issue age output.
     *
     * @param $issue
     * @return string
     */
    protected function _formatIssueAge($issue)
    {
        $issueAge = ($issue->fields->resolutiondate) ? new \DateTime($issue->fields->resolutiondate) : new \DateTime();
        $issueAge = ($issueAge->getTimestamp() - $issue->fields->created->getTimestamp()) / 3600;

        if ($issueAge > 48) {
            $ageUnit = 'd';
            $ageValue = floor($issueAge / 24);
        } else {
            $ageUnit = 'h';
            $ageValue = floor($issueAge);
        }

        return $ageValue . $ageUnit;
    }

    /**
     * Get shorten summary.
     *
     * @param $summery
     * @param int $len
     * @return string
     */
    protected function _getShortSummery($summery, $len = 30)
    {
        if (strlen($summery) > ($len-1)) {
            $shortName = mb_substr($summery, 0, ($len-1)) . '...';
        } else {
            $shortName = $summery;
        }

        return '<span title="' . str_replace('"', '&quot;', $summery) . '">' . htmlentities($shortName) . '</span>';
    }

    /**
     * Get shorten user name.
     *
     * @param $name
     * @param int $len
     * @return string
     */
    protected function _getShortName($name, $len = 10)
    {
        $split = explode(' ', $name, 2);
        if (count($split) == 1) {
            $split = explode('.', $name, 2);
        }

        if (count($split) == 1) {
            $shortName = ucfirst($split[0]);
        } else {
            $shortName = substr(ucfirst($split[0]), 0, 1) . '.' . ucfirst($split[1]);
        }

        if (strlen($shortName) > ($len-1)) {
            $shortName = mb_substr($shortName, 0, ($len-1)) . '...';
        }

        return '<span title="' . str_replace('"', '&quot;', $name) . '">' . htmlentities($shortName) . '</span>';
    }

    /**
     * Create link.
     *
     * @param $key
     * @param $text
     * @return string
     */
    protected function _createLink($key, $text)
    {
        $jiraLogin = $this->get('JiraCoreService')->getLoginCredentials();

        return '<a href="' . $jiraLogin->getJiraHost() . '/browse/' . $key
            . '" target="_blank" title="' . str_replace('"', '&quot;', $text) . '">' . $key . '</a>';
    }
}
