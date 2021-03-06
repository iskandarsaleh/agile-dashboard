<?php

namespace RGies\JiraCountWidgetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JiraRestApi\Issue\IssueService;
use JiraRestApi\Configuration\ArrayConfiguration;
use JiraRestApi\JiraException;

/**
 * Widget controller.
 *
 * @Route("/jira_count_widget")
 */
class DefaultController extends Controller
{
    /**
     * Collect needed widget data.
     *
     * @Route("/collect-data/", name="JiraCountWidgetBundle-collect-data")
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


        // Data cache
        $cache = $this->get('CacheService');
        if ($cacheValue = $cache->getValue('JiraCountWidget', $widgetId, null, $updateInterval)) {
            //return new Response($cacheValue, Response::HTTP_OK);
        }

        $jiraLogin = $this->get('JiraCoreService')->getLoginCredentials();
        $widgetConfig = $this->get('WidgetService')->getResolvedWidgetConfig($widgetType, $widgetId);

        $response = array();
        $response['icon'] = $widgetConfig->getIcon();

        $jql = $widgetConfig->getJqlQuery();

        try {
            $issueService = new IssueService($jiraLogin);
            $issues = $issueService->search($jql, 0, 100000, ['key']);
        } catch (JiraException $e) {
            $response['warning'] = wordwrap($e->getMessage(), 38, '<br/>');
            return new Response(json_encode($response), Response::HTTP_OK);
        }

        if ($issues) {
            $response['value'] = $issues->getTotal();
        }

        $response['link'] = $jiraLogin->getJiraHost() . '/issues/?jql=' . urlencode($jql);

        $cache->setValue('JiraCountWidget', $widgetId, json_encode($response));

        return new Response(json_encode($response), Response::HTTP_OK);
    }

}
