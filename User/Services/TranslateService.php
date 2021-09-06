<?php
namespace User\Services;

use User\Repository\TranslateRepository;

class TranslateService
{
    private $gateway_translate_repository;

    public function __construct(TranslateRepository $gateway_translate_repository)
    {
        $this->gateway_translate_repository = $gateway_translate_repository;
    }

    public function list()
    {
        return $this->gateway_translate_repository->list();
    }
}
