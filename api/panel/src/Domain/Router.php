<?php

namespace Alternc\API\Domain;

use Alternc\API\APIResponse;
use Alternc\API\Auth\User;
use AltoRouter;
use Doctrine\DBAL\Connection;

class Router {
    public function __construct(AltoRouter $router) {
        $router->map("GET", "/domains", [$this, "get_all_domains"]);
        $router->map("POST", "/domains", [$this, "add_domain"]);
        $router->map("GET", "/domains/[*:domain_name]", [$this, "get_domain_by_name"]);
        $router->map("DELETE", "/domains/[*:domain_name]", [$this, "delete_domain"]);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function get_all_domains(User $user, Connection $db): APIResponse
    {
        $query_builder = Domain::query_builder($db);

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
    public function get_domain_by_name(User $user, Connection $db, string $domain_name): APIResponse
    {
        $query_builder = Domain::query_builder($db)
            ->where("domaine = :domain_name")
            ->setParameter("domain_name", $domain_name);

        if (!$user->is_admin) {
            $query_builder = $query_builder
                ->where("compte = :uid")
                ->setParameter("uid", $user->uid);
        }

        $domain = $query_builder->fetchAssociative();

        if (empty($domain)) {
            return APIResponse::not_found(["error" => "Domain not found"]);
        }

        return APIResponse::ok(["domain" => new Domain(...$domain)]);
    }

    public function add_domain(User $user): APIResponse
    {
        global $dom;

        if (!isset($_POST["domain_name"]) || !isset($_POST["dns"])) {
            return APIResponse::bad_request(["error" => "Missing required parameters"]);
        }

        $domain_name = $_POST["domain_name"];
        $dns = post_bool($_POST["dns"]);

        $is_secondary = post_bool("is_secondary");
        $secondary_domain = $_POST["secondary_domain"] ?? "";
        $no_erase = false;
        $force = false;

        if ($user->is_admin) {
            $no_erase = post_bool("no_erase");
            $force = post_bool("force");
        }

        $dom->lock();
        $domain_id = $dom->add_domain(
            $domain_name,
            $dns,
            $no_erase,
            $force,
            $is_secondary,
            $secondary_domain,
        );
        $dom->unlock();

        if (!$domain_id) {
            global $msg;
            return APIResponse::internal_server_error(["error" => $msg->msg_html_all()]);
        }

        return APIResponse::ok(["domain_id" => $domain_id]);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function delete_domain(User $user, Connection $db, string $domain_name): APIResponse
    {
        global $dom;

        if (!$user->is_admin) {
            $query = Domain::query_builder($db)
                ->where("domaine = :domain_name")
                ->where("compte = :uid")
                ->setParameter("domain_name", $domain_name)
                ->setParameter("uid", $user->uid);
            $query = $query->fetchAssociative();
            if (empty($query)) {
                return APIResponse::forbidden(["error" => "You are not allowed to delete this domain"]);
            }
        }

        $result = $dom->del_domain($domain_name);

        if (!$result) {
            global $msg;
            return APIResponse::internal_server_error(["error" => $msg->msg_html_all()]);
        }

        return APIResponse::ok(["status" => "ok"]);
    }
}