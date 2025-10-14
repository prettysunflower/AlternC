<?php

namespace Alternc\API\Domain;

use Doctrine\DBAL\Connection;

class Domain {
    public int $id;
    public string $name;
    public string $owner_uid;
    public bool $managed_dns;
    public bool $managed_mx;
    public bool $no_erase;
    public string $dns_action;
    public int $dns_result;
    public int $zone_ttl;

    /**
     * @param int $id
     * @param string $name
     * @param string $owner_uid
     * @param bool $managed_dns
     * @param bool $managed_mx
     * @param bool $no_erase
     * @param string $dns_action
     * @param int $dns_result
     * @param int $zone_ttl
     */
    public function __construct(
        int $id,
        string $name,
        string $owner_uid,
        bool $managed_dns,
        bool $managed_mx,
        bool $no_erase,
        string $dns_action,
        int $dns_result,
        int $zone_ttl,
    ) {
        $this->id          = $id;
        $this->name        = $name;
        $this->owner_uid   = $owner_uid;
        $this->managed_dns = $managed_dns;
        $this->managed_mx  = $managed_mx;
        $this->no_erase    = $no_erase;
        $this->dns_action  = $dns_action;
        $this->dns_result  = $dns_result;
        $this->zone_ttl    = $zone_ttl;
    }

    public function __toString() {
        return $this->name;
    }

    public static function query_builder(Connection $db)
    {
        return $db
            ->createQueryBuilder()
            ->select(
                'id',
                'domaine as name',
                'compte as owner_uid',
                'gesdns as managed_dns',
                'gesmx as managed_mx',
                'noerase as no_erase',
                'dns_action',
                'dns_result',
                'zonettl as zone_ttl'
            )
            ->from('domaines')
            ->orderBy('name');
    }

    static function from_ids(array $ids, Connection $db): array
    {
        $queryBuilder = self::query_builder($db)
            ->where('id IN (:ids)')
            ->setParameter('ids', $ids);

        return array_map(function($domain) {
            return new Domain(...$domain);
        }, $queryBuilder->fetchAllAssociative());
    }
}