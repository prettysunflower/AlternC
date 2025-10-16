<?php

namespace Alternc\API\Email;

use Doctrine\DBAL\Connection;

class Email {
    public int $id;
    public string $address;
    public string $domain;
    public bool $enabled;
    public ?string $path;
    public ?int $quota;
    public ?int $quota_bytes;
    public ?int $used;
    public bool $is_local;
    public string $type;
    public ?string $last_login;
    public array $recipients;

    /**
     * @param int $id
     * @param string $address
     * @param bool $enabled
     * @param string $domain
     * @param string $path
     * @param int $quota
     * @param int $used
     * @param bool $islocal
     * @param string $type
     */
    public function __construct(
        int $id,
        string $address,
        bool $enabled,
        string $domain,
        ?string $path,
        ?int $quota,
        ?int $used,
        bool $is_local,
        string $type,
        ?string $last_login,
        ?string $recipients
    ) {
        $this->id         = $id;
        $this->address    = $address;
        $this->enabled    = $enabled;
        $this->domain     = $domain;
        $this->path       = $path;
        $this->quota      = $quota;
        if ($quota) {
            $this->quota_bytes = $quota * 1024 * 1024;
        }
        $this->used       = $used;
        $this->is_local    = $is_local;
        $this->type       = $type;
        $this->last_login = $last_login;
        if ($recipients) {
            $this->recipients = array_filter(
                preg_split("/\r\n|\n|\r/", $recipients)
            );
        } else {
            $this->recipients = [];
        }
    }

    public static function query_builder(Connection $db)
    {
        return $db
            ->createQueryBuilder()
            ->select(
                'a.id',
                'a.address',
                'a.enabled',
                'd.domaine AS domain',
                'm.path',
                'm.quota',
                'q.quota_dovecot AS used',
                'NOT ISNULL(m.id) AS is_local',
                'a.type',
                'm.lastlogin as last_login',
                'r.recipients'
            )
            ->from('address', 'a')
            ->leftJoin('a', 'domaines', 'd', 'd.id = a.domain_id')
            ->leftJoin('a', 'mailbox', 'm', 'm.address_id = a.id')
            ->leftJoin('a', 'dovecot_quota', 'q', 'CONCAT(a.address, "@", d.domaine) = q.user')
            ->leftJoin('a', 'recipient', 'r', 'r.address_id = a.id')
            ->orderBy('address');
    }
}