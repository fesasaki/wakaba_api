<?php

namespace App\Helper;

use Illuminate\Support\Facades\Request;

class IP
{

    /**
     * Retorna todos os endereços ip da solicitação
     * Ip do cliente e possiveis ips de proxies
     *
     * @return string $ip
     */
    public static function getIP()
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? Request::ip();
    }

    /**
     * Retorna o endereço IP da solicitação cliente
     * Quando acesso passa por proxy o endereço do cliente é enviado pelo parametro X-Forwarded-For
     * Dependendo da configuração do proxy ele pode adicionar seu proprio ip ao X-Forwarded-For (separados por virgula)
     * O primeiro IP refere-se ao IP da requisição vinda do cliente e os demais (caso existam) o IPs dos proxies
     *
     * @return string $ip
     */
    public static function getIPClient()
    {
        $ip = self::getIP();

        return explode(",", $ip)[0];
    }

}
