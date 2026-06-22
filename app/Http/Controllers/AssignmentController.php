<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Imports\AssignmentImport;
use Maatwebsite\Excel\Facades\Excel;

class AssignmentController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:50000',
        ]);

        try {
            Assignment::truncate();
            Excel::import(new AssignmentImport, $request->file('file'));
            
            // Map the names using targets table mapping (like python script did)
            // Or we just rely on whatever is in the targets table. 
            // The python script dynamically mapped assigned_ppl_name based on level_6_full_code.
            // Let's do that post-import!
            $mappings = \App\Models\Target::where('type', 'sls')->get()->keyBy('key');
            $assignments = Assignment::whereNotNull('level_6_full_code')->get();
            foreach ($assignments as $a) {
                $sls = $a->level_6_full_code;
                if (str_ends_with($sls, '.0')) {
                    $sls = substr($sls, 0, -2);
                }
                if ($mappings->has($sls)) {
                    $meta = $mappings[$sls]->meta;
                    $a->assigned_ppl_name = $meta['ppl_name'] ?? '';
                    $a->assigned_pml_name = $meta['pml_name'] ?? '';
                    $a->save();
                }
            }

            return redirect()->back()->with('success', 'Data berhasil diunggah dan diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
