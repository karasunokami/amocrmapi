<?php

declare(strict_types=1);

namespace Amocrmapi\Entity;

use Amocrmapi\Traits\DefaultEntityTrait;
use Amocrmapi\Dependencies\EntityInterface;

class Company implements EntityInterface
{
    use DefaultEntityTrait;

    const COMPANY_DEFAULT_NAME = "api company";
    const ELEMENT_TYPE = 3;

    public function __construct()
    {
        $this->entity = [
            "id" => null,
            "group_id" => null,
            "account_id" => null,
            "created_at" => null,
            "updated_at" => null,
            "created_by" => null,
            "updated_by" => null,
            "closest_task_at" => null,
            "responsible_user_id" => null,
            "name" => self::COMPANY_DEFAULT_NAME,
            
            "tags" => [],
            "tasks" => [],
            "notes" => [],
            "custom_fields" => [],
            "leads" => ["id" => []],
            "contacts" => ["id" => []],
            "customers" => ["id" => []],
            
            "unlink" => null,
        ];
    }
    
    /**
     * Prepare entity to sync with amocrm
     * 
     * @return array
     */
    public function prepare() : array
    {
        $this->entity["tags"] = join(",", array_column($this->entity["tags"], "name"));
        
        $this->entity["contacts_id"] = $this->entity["contacts"]["id"];
        $this->entity["leads_id"] = $this->entity["leads"]["id"];

        return $this->entity;
    }

    /**
     * Parse lead entity from amocrm response
     * 
     * @param array @data
     * 
     * @return \Amocrmapi\Entity\Company
     */
    public function parse(array $data) : \Amocrmapi\Entity\Company
    {
        $data["tags"] = array_reverse(array_column($data["tags"], "name"));
        $this->entity = $data;
        
        return $this;
    }

    /**
     * Bind lead to company
     * 
     * @param int $id
     */
    public function addLead(int $id)
    {
        $this->entity["leads"]["id"][] = $id;

        return $this;
    }

    /**
     * Bind contact to company
     */
    public function addContact(int $id)
    {
        $this->entity["contacts"]["id"][] = $id;

        return $this;
    }

    /**
     * Set entity updated_by
     * 
     * @param int
     */
    public function setUpdatedBy(int $updatedBy)
    {
        $this->entity["updated_by"] = $updatedBy;

        return $this;
    }

    /**
     * Return entity updated_by
     * 
     * @return int
     */
    public function getUpdatedBy()
    {
        return $this->entity["updated_by"];
    }

    /**
     * Return entity group_id
     * 
     * @return int
     */
    public function getGroupId()
    {
        return $this->entity["group_id"];
    }

    /**
     * Return entity leads
     * 
     * ["id" => int]
     * 
     * @return array
     */
    public function getLeads()
    {
        return $this->entity["leads"];
    }

    /**
     * Return entity contacts
     * 
     * ["id" => int]
     * 
     * @return array
     */
    public function getContacts()
    {
        return $this->entity["contacts"];
    }
}