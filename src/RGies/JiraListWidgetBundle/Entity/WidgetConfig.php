<?php

namespace RGies\JiraListWidgetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * WidgetConfig
 *
 * @ORM\Table(name="JiraListWidgetConfig")
 * @ORM\Entity(repositoryClass="RGies\JiraListWidgetBundle\Entity\WidgetConfigRepository")
 */
class WidgetConfig
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="widget_id", type="integer")
     */
    private $widget_id;

    /**
     * @var string
     *
     * @ORM\Column(name="jql_query", type="text")
     */
    private $jql_query;

    /**
     * @var string
     *
     * @ORM\Column(name="extended_info", type="text", length=100, nullable=true)
     */
    private $extended_info = 'summary';

    /**
     * Constructor
     */
    public function __construct($init = null)
    {
        if ($init !== null)
        {
            foreach ((array)$init as $key=>$value)
            {
                $this->$key = $value;
            }
            $this->id = null;
        }
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set widgetId
     *
     * @param integer $widgetId
     *
     * @return WidgetConfig
     */
    public function setWidgetId($widgetId)
    {
        $this->widget_id = $widgetId;

        return $this;
    }

    /**
     * Get widgetId
     *
     * @return integer
     */
    public function getWidgetId()
    {
        return $this->widget_id;
    }

    /**
     * Set jqlQuery
     *
     * @param string $jqlQuery
     *
     * @return WidgetConfig
     */
    public function setJqlQuery($jqlQuery)
    {
        $this->jql_query = $jqlQuery;

        return $this;
    }

    /**
     * Get jqlQuery
     *
     * @return string
     */
    public function getJqlQuery()
    {
        return $this->jql_query;
    }

    /**
     * Set extendedInfo
     *
     * @param string $extendedInfo
     *
     * @return WidgetConfig
     */
    public function setExtendedInfo($extendedInfo)
    {
        $this->extended_info = $extendedInfo;

        return $this;
    }

    /**
     * Get extendedInfo
     *
     * @return string
     */
    public function getExtendedInfo()
    {
        return $this->extended_info;
    }
}
