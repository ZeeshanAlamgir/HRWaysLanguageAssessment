<?php
namespace App\Services\Translation;

use App\Models\Translation;
use Illuminate\Support\Facades\Auth;

class TranslationService implements TranslationInterface
{
    public function index($request)
    {
        $translations = Translation::select('key','locale','value','tag')
        ->when($request->filled('key'), function ($query) use ($request) {
            return $query->where('key', 'like', '%' . $request->key . '%');
        })
        ->when($request->filled('tag'), function ($query) use ($request) {
            return $query->where('tag', $request->tag);
        })
        ->when($request->filled('locale'), function ($query) use ($request) {
            return $query->where('locale', $request->locale);
        })
        ->when($request->filled('value'), function ($query) use ($request) {
            return $query->where('value', $request->value);
        });
        $translations = $translations->paginate(10);
        return $translations;
    }

    public function store($validated)
    {
        $translation = Translation::updateOrCreate(
            [
                'key' => $validated['key'],
                'locale' => $validated['locale'],
                'tag' => $validated['tag'],
            ],
            [
                'value' => $validated['value'],
            ]
        );
        return $translation;
    }

    public function show($request)
    {
        $searchKey = $request->input('key');

        $translations = Translation::select('key', 'locale', 'value', 'tag')
        ->where(function ($query) use ($searchKey) {
            $query->where('key', $searchKey)
                ->orWhere('locale', $searchKey)
                ->orWhere('tag', $searchKey);
        })
        ->orderBy('id')
        ->paginate(10);
        if(count($translations) == 0)
            return null;
        $groupedData = collect($translations->items())
            ->groupBy('locale')
            ->map(function ($group) {
                return $group->groupBy('key')->map(function ($translations) {
                    return $translations->pluck('value');
                });
            });
        if ($groupedData)
            return [$groupedData,$translations];
        else
            return null;
    }
    public function exportJson($request)
    {
        return Translation::query()
        ->when($request->filled('locale'), function ($query) use ($request) {
            return $query->where('locale', $request->locale);
        })
        ->when($request->filled('tag'), function ($query) use ($request) {
            return $query->where('tag', $request->tag);
        })
        ->get()
        ->groupBy('locale')
        ->map(function ($group) {
            return $group->pluck('value', 'key');
        });

    }

    public function delete($id)
    {
        $translation = Translation::find($id);
        if ($translation)
        {
            $translation->delete();
            return true;
        }
        else
            return false;
    }

    public function update($validated,$id)
    {
        $translation = Translation::updateOrCreate(
            [
                'id' => $id,
            ],
            [
                'key' => $validated['key'],
                'locale' => $validated['locale'],
                'tag' => $validated['tag'],
                'value' => $validated['value'],
            ]
        );
        return $translation;
    }
}
