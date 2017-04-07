<?php

namespace RGies\SeparatorWidgetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * WidgetConfig
 *
 * @ORM\Table(name="SeparatorWidgetConfig")
 * @ORM\Entity(repositoryClass="RGies\SeparatorWidgetBundle\Entity\WidgetConfigRepository")
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
     * @ORM\Column(name="datarow1", type="string", length=255, nullable=true)
     */
    private $datarow1;


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
     * Set datarow1
     *
     * @param string $datarow1
     *
     * @return WidgetConfig
     */
    public function setDatarow1($datarow1)
    {
        $this->datarow1 = $datarow1;

        return $this;
    }

    /**
     * Get datarow1
     *
     * @return string
     */
    public function getDatarow1()
    {
        return $this->datarow1;
    }
}
