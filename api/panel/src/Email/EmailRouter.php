<?php

namespace Alternc\API\Email;

use Alternc\API\APIResponse;
use Alternc\API\Auth\User;
use Alternc\API\Domain\Domain;
use Alternc\API\RequestType;
use Alternc\API\Router;
use AltoRouter;
use Doctrine\DBAL\Connection;

class EmailRouter extends Router {
    public function __construct(AltoRouter $router) {
        $router->addRoutes(routes: [
            RequestType::GET->route("/emails", [$this, "get_all_mx_managed_domains"]),
            RequestType::GET->route("/emails/[*:domain_name]", [$this, "get_email_addresses_for_domain"]),
        ]);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function get_all_mx_managed_domains(User $user, Connection $db): APIResponse
    {
        $query_builder = Domain::query_builder($db);

        $query_builder = $query_builder
            ->where("gesmx = 1");

        if (!$user->is_admin) {
            $query_builder = $query_builder
                ->where("compte = :uid")
                ->setParameter("uid", $user->uid);
        }

        $domains = array_map(function($domain) {
            return new Domain(...$domain);
        }, $query_builder->fetchAllAssociative());

        return APIResponse::ok(["domains" => $domains]);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function get_email_addresses_for_domain(User $user, Connection $db, string $domain_name): APIResponse
    {
        $query_builder = Email::query_builder($db)
            ->where("d.domaine = :domain_name")
            ->setParameter("domain_name", $domain_name);

        if (!$user->is_admin) {
            $query_builder = $query_builder
                ->where("d.compte = :uid")
                ->setParameter("uid", $user->uid);
        }

        error_log($query_builder->getSQL());

        $emails = array_map(function($email) {
            return new Email(...$email);
        }, $query_builder->fetchAllAssociative());

        return APIResponse::ok(["emails" => $emails]);
    }
}