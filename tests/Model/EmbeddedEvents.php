<?php

namespace Model;

/**
 * Model\EmbeddedEvents document.
 */
class EmbeddedEvents extends \Model\Base\EmbeddedEvents
{
    protected $events = array();
    protected $myEventPrefix;

    public function getEvents()
    {
        return $this->events;
    }

    public function clearEvents()
    {
        $this->events = array();

        return $this;
    }

    public function setMyEventPrefix($prefix)
    {
        $this->myEventPrefix = $prefix;

        return $this;
    }

    public function getMyEventPrefix()
    {
        return $this->myEventPrefix;
    }

    public function addEvent($id)
    {
        $this->events[] = $this->myEventPrefix.$id.(int)$this->isModified();
    }

    protected function myPreInsert()
    {
        $this->addEvent('PreInserting');
    }

    protected function myPostInsert()
    {
        $this->addEvent('PostInserting');
    }

    protected function myPreUpdate()
    {
        $this->addEvent('PreUpdating');
        $this->setName('preUpdating');
    }

    protected function myPostUpdate()
    {
        $this->addEvent('PostUpdating');
    }

    protected function myPreDelete()
    {
        $this->addEvent('PreDeleting');
    }

    protected function myPostDelete()
    {
        $this->addEvent('PostDeleting');
    }
}