<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTranslationRequest;
use App\Services\Translation\TranslationInterface;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    public function __construct(protected TranslationInterface $translationService)
    {
        $this->translationService = $translationService;
    }
    public function index(Request $request)
    {
        $translations = $this->translationService->index($request);
        if (count($translations)<=0) {
            return apiResponse('No Data Found', false);
        }
        return apiResponse('Data Found', true, $translations);
    }

    public function store(StoreTranslationRequest $request)
    {
        $translation = $this->translationService->store($request->only('key','locale','value','tag'));
        return apiResponse('Data Stored Successfully', true, $translation);
    }

    public function show(Request $request)
    {
        $response = $this->translationService->show($request);
        if(is_null($response))
        {
            return response()->json([
                'success' => false,
                'message' => 'Data Not Found',
                'data' => null,
                'meta' => null
            ]);
        }
        $groupedData = $response[0];
        $translations = $response[1];
        if (count($translations)<=0)
        {
            return response()->json([
                'success' => false,
                'message' => 'Data Not Found',
                'data' => null,
                'meta' => null
            ]);
        }
        else
        {
            return response()->json([
            'success' => true,
            'message' => 'Translations fetched successfully.',
            'data' => $groupedData,
            'meta' => [
                'current_page' => $translations->currentPage(),
                'last_page' => $translations->lastPage(),
                'per_page' => $translations->perPage(),
                'total' => $translations->total(),
                ]
            ]);
        }
    }


    public function exportJson(Request $request)
    {
        $translation = $this->translationService->exportJson($request);
        if(!$translation)
            return apiResponse('Data Not Found', false, null);
        else
            return apiResponse('Data Found', true, $translation);
    }

    public function update(StoreTranslationRequest $request,$id)
    {
        $translation = $this->translationService->update($request->only('key','locale','value','tag'),$id);
        return apiResponse('Data Updated Successfully', true, $translation);
    }
}

