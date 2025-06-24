<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DictionaryController extends Controller
{
    public function search(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'q' => 'required|string|min:1',
            ], [
                'q.required' => 'Search query cannot be empty.',
            ]);

            if ($validator->fails()) {
                return $this->translateFailedResponse(400, $validator->messages()->first());
            }

            $query = $request->q;
            $results = Http::get("https://api.dictionaryapi.dev/api/v2/entries/en/$query");

            if (strpos($results, 'No Definitions Found') !== false) {
                return $this->translateFailedResponse(404, 'No definitions found');
            }

            $results = $results->json();

            $data = [];
            foreach ($results as $index => $result) {
                $data[$index]['word'] = $result['word'];
                $data[$index]['phonetics'] = $this->extractPhonetics($result);
                $data[$index]['definitions'] = $this->extractDefinitions($result);
                $data[$index]['examples'] = $this->extractExamples($result);
                $data[$index]['partOfSpeech'] = $this->extractPartOfSpeech($result);
                $data[$index]['synonyms'] = $this->extractSynonyms($result);
            }


            return $this->translateSuccessResponse(null, $data);
        } catch (\Exception $e) {
            return $this->translateExceptionResponse($e);
        }
    }

    public function extractPhonetics($result)
    {
        $unique = [];
        $phonetics = [];

        foreach ($result['phonetics'] as $item) {
            if (!empty($item['audio']) && !empty($item['text'])) {
                $key = $item['text'];

                if (!isset($unique[$key])) {
                    $unique[$key] = true;

                    if (isset($item['sourceUrl'])) {
                        unset($item['sourceUrl']);
                    }
                    if (isset($item['license'])) {
                        unset($item['license']);
                    }

                    $phonetics[] = $item;
                }
            }
        }

        return $phonetics;
    }

    public function extractDefinitions($result)
    {
        $meaning = [];
        if (!empty($result['meanings'][0])) {
            $meaning = $result['meanings'][0];
        }

        return array_map(function ($def) {
            return $def['definition'];
        }, $meaning['definitions']);
    }

    public function extractExamples($result)
    {
        $meaning = [];
        if (!empty($result['meanings'][0])) {
            $meaning = $result['meanings'][0];
        }

        $examples = array_map(function ($def) {
            return isset($def['example']) ? $def['example'] : null;
        }, $meaning['definitions']);

        return array_values(array_filter($examples, fn($ex) => !is_null($ex)));
    }

    public function extractPartOfSpeech($result)
    {
        $meaning = [];
        if (!empty($result['meanings'][0])) {
            $meaning = $result['meanings'][0];
        }

        return $meaning['partOfSpeech'];
    }

    public function extractSynonyms($result)
    {
        $meaning = !empty($result['meanings'][0]) ? $result['meanings'][0] : [];
        $synonyms = [];
        if (!empty($meaning['definitions'])) {
            foreach ($meaning['definitions'] as $def) {
                if (!empty($def['synonyms']) && is_array($def['synonyms'])) {
                    foreach ($def['synonyms'] as $synonym) {
                        $synonyms[] = $synonym;
                    }
                }
            }
        }
        return array_values(array_unique($synonyms));
    }
}
