<?php

declare(strict_types=1);

namespace Amocrmapi\Entity;

use Amocrmapi\Traits\DefaultEntityTrait;
use Amocrmapi\Dependencies\EntityInterface;

class Contact implements EntityInterface
{
    use DefaultEntityTrait;
	
    const CONTACT_DEFAULT_NAME = "api contact";
    const ELEMENT_TYPE = 1;

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
            "name" => self::CONTACT_DEFAULT_NAME,

            "tags" => [],
            "notes" => [],
            "tasks" => [],
            "custom_fields" => [],
            "leads" => ["id" => []],
            "company" => ["id" => []],
            "customers" => ["id" => []],

            "unlink" => null
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
        
        $this->entity["leads_id"] = $this->entity["leads"]["id"];
        $this->entity["company_id"] = $this->entity["company"]["id"];

        return $this->entity;
    }

    /**
     * Parse lead entity from amocrm response
     * 
     * @param array @data
     * 
     * @return \Amocrmapi\Entity\Contact
     */
    public function parse(array $data) : \Amocrmapi\Entity\Contact
    {
        $data["tags"] = array_reverse(array_column($data["tags"], "name"));
        $this->entity = $data;
        
        return $this;
    }

    /**
     * Set first contact phone
     * 
     * @param array $customFields - all custom fields of contacts
     * of contact (AccountApi::getAccountInfo()["custom_fields"]["contacts"])
     * @param string $phone
     * @param string $enum = "WORK" - id of enum or one of
     * ["WORK", "WORKDD", "MOB", "FAX", "HOME", "OTHER"]
     */
    public function setPhone(array $customFields, string $phone, string $enum = "WORK")
    {
        $id = $this->findPhoneId($customFields);
        $this->setCustomField($id, $phone, $enum);

        return $this;
    }

    /**
     * Add phone to exist phone numbers
     * 
     * @param array $customFields - all custom fields of contacts
     * of contact (AccountApi::getAccountInfo()["custom_fields"]["contacts"])
     * @param string $phone
     * @param string $enum = "WORK" - id of enum or one of
     * ["WORK", "WORKDD", "MOB", "FAX", "HOME", "OTHER"]
     */
    public function addPhone(array $customFields, string $phone, string $enum = "WORK")
    {
        $id = $this->findPhoneId($customFields);
        $index = array_search($id, array_column($this->entity["custom_fields"], "id"));
        
        if ($index !== false) {
            $this->entity["custom_fields"][$index]["values"][] = [
                "value" => $phone,
                "enum" => $enum
            ];

            return $this;
        }

        $this->setCustomField($id, $phone, $enum);

        return $this;
    }

    /**
     * Get all contact phones
     * 
     * @param array $customFields - all custom fields of contacts
     * of contact (AccountApi::getAccountInfo()["custom_fields"]["contacts"])
     * 
     * @return array $contactPhones
     */
    public function getPhones(array $customFields) : array
    {
        $id = $this->findPhoneId($customFields);
        $index = array_search($id, array_column($this->entity["custom_fields"], "id"));

        if ($index !== false) {
            return array_reverse($this->entity["custom_fields"][$index]["values"]);
        }

        return [];
    }

    /**
     * Set first email address
     * 
     * @param array $customFields - all custom fields of contacts
     * of contact (AccountApi::getAccountInfo()["custom_fields"]["contacts"])
     * @param string $phone
     * @param string $enum - id of enum or one of
     * ["WORK", "PRIV", "OTHER"]
     */
    public function setEmail(array $customFields, string $email, string $enum = "WORK")
    {
        $id = $this->findEmailId($customFields);
        $this->setCustomField($id, $email, $enum);

        return $this;
    }

    /**
     * Add email to exist email addresses
     * 
     * @param array $customFields - all custom fields of contacts
     * of contact (AccountApi::getAccountInfo()["custom_fields"]["contacts"])
     * @param string $email
     */
    public function addEmail(array $customFields, string $email, string $enum = "WORK")
    {
        $id = $this->findEmailId($customFields);
        $index = array_search($id, array_column($this->entity["custom_fields"], "id"));
        
        if ($index !== false) {
            $this->entity["custom_fields"][$index]["values"][] = [
                "value" => $email,
                "enum" => $enum
            ];

            return $this;
        }

        $this->setCustomField($id, $email, $enum);

        return $this;
    }

    /**
     * Get all contact emails
     * 
     * @param array $customFields - all custom fields of contacts
     * of contact (AccountApi::getAccountInfo()["custom_fields"]["contacts"])
     * 
     * @return array $contactEmails
     */
    public function getEmails(array $customFields) : array
    {
        $id = $this->findEmailId($customFields);
        $index = array_search($id, array_column($this->entity["custom_fields"], "id"));

        if ($index !== false) {
            return $this->entity["custom_fields"][$index]["values"];
        }

        return [];
    }

    /**
     * Bind lead to contact
     * 
     * @param int $id
     */
    public function addLead(int $id)
    {
        $this->entity["leads"]["id"][] = $id;
        
        return $this;   
    }

    /**
     * Bind company to contact
     *
     * @param int $id
     * 
     * @return array
     */
    public function setCompany(int $id)
    {
        $this->entity["company"]["id"] = $id;

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
     * Return entity company
     * 
     * ["id" => int, "name" => string]
     * 
     * @return array
     */
    public function getCompany()
    {
        return $this->entity["company"];
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
     * Return entity customers
     * 
     * ["id" => int]
     * 
     * @return array
     */
    public function getCustomers()
    {
        return $this->entity["customers"];
    }

    /**
     * Get id of system phone custom field
     * 
     * @param array $contactCustomFields
     */
    private function findPhoneId($contactCustomFields)
    {
        foreach ($contactCustomFields as $field) {
            if (
                $field["is_system"]
                && ($field["name"] == "Телефон" || $field["name"] == "Phone")
            ) {
                return $field["id"];
            }
        }
    }

    /**
     * Get id of system email custom field
     * 
     * @param array $contactCustomFields
     */
    private function findEmailId($contactCustomFields)
    {
        foreach ($contactCustomFields as $field) {
            if ($field["is_system"] && $field["name"] == "Email") {
                return $field["id"];
            }
        }
    }
}