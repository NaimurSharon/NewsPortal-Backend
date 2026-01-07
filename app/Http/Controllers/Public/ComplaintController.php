<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ComplaintController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'article_link' => 'nullable|url',
            'type' => 'required|string',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $complaint = Complaint::create([
            'name' => $request->name,
            'email' => $request->email,
            'article_link' => $request->article_link,
            'type' => $request->type,
            'description' => $request->description,
            'ip_address' => $request->ip(),
            'status' => 'new'
        ]);

        return response()->json([
            'message' => 'আপনার অভিযোগটি সফলভাবে জমা দেওয়া হয়েছে। আমরা এটি অত্যন্ত গুরুত্বের সাথে পর্যালোচনা করব।',
            'data' => $complaint
        ], 201);
    }
}
