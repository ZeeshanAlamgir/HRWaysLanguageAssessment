<?php

namespace App\Services\Translation;

interface TranslationInterface
{
    public function index($request);
    public function store($request);
    public function show($request);
    public function exportJson($request);
    public function update($request,$id);
    public function delete($id);
}
