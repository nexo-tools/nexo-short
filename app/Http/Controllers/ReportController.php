<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

/**
 * Public abuse report channel on the short host (ADR-005 §7): no auth, rate
 * limited, cookieless (so validation is handled inline — there is no session to
 * flash errors to). Reachable from the branded 404 and the landing. noindex.
 */
class ReportController extends Controller
{
    public function show(Request $request): Response
    {
        return response()->view('report', [
            'slug' => (string) $request->query('slug', ''),
            'sent' => false,
            'invalid' => false,
        ]);
    }

    public function store(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'slug' => ['required', 'string', 'max:32'],
            'reason' => ['required', Rule::in(array_keys(config('nexo.report_reasons')))],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return response()->view('report', [
                'slug' => (string) $request->input('slug', ''),
                'sent' => false,
                'invalid' => true,
            ], 422);
        }

        Report::create($validator->validated() + ['created_at' => now()]);

        return response()->view('report', ['slug' => '', 'sent' => true, 'invalid' => false]);
    }
}
