<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contribution;
use Illuminate\Http\Request;

class ContributeController extends Controller
{
    public function index()
    {
        $contributions = Contribution::orderBy('created_at', 'desc')->paginate(20);
        return response()->json($contributions);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,reviewed,published,rejected',
        ]);

        $contribution = Contribution::findOrFail($id);
        $contribution->update([
            'status' => $request->status
        ]);

        return response()->json([
            'message' => 'Contribution status updated successfully',
            'contribution' => $contribution
        ]);
    }

    public function destroy($id)
    {
        $contribution = Contribution::findOrFail($id);
        $contribution->delete();

        return response()->json(['message' => 'Contribution deleted successfully']);
    }
}
