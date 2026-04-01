<?php

namespace App\Http\Controllers;

use App\Models\Logbook;
use App\Models\User;
use App\Models\InternshipApplication;
use App\Models\DivisiAdmin;
use App\Models\DivisionMentor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogbookController extends Controller
{
    /**
     * Display logbook page for peserta (student)
     */
    public function index()
    {
        $user = Auth::user();

        // Get active application
        $application = $user->internshipApplications()
            ->whereIn('status', ['accepted', 'finished'])
            ->latest()
            ->first();

        if (!$application) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda belum memiliki pengajuan magang yang diterima.');
        }

        // Get existing logbooks ordered by date descending with pagination
        $logbooks = Logbook::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->paginate(10);

        return view('logbook.index', compact('logbooks'));
    }
    
    /**
     * Store a new logbook entry
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'content' => 'required|string',
        ]);
        
        $user = Auth::user();
        
        // Check if logbook for this date already exists
        $existingLogbook = Logbook::where('user_id', $user->id)
            ->whereDate('date', $request->date)
            ->first();
        
        if ($existingLogbook) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Logbook untuk tanggal ini sudah ada.'
                ], 422);
            }
            return redirect()->back()
                ->with('error', 'Logbook untuk tanggal ini sudah ada.');
        }
        
        $logbook = Logbook::create([
            'user_id' => $user->id,
            'date' => $request->date,
            'content' => $request->content,
        ]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logbook berhasil disimpan.',
                'logbook' => $logbook
            ]);
        }
        
        return redirect()->route('logbook.index')
            ->with('success', 'Logbook berhasil disimpan.');
    }
    
    /**
     * Update an existing logbook entry
     */
    public function update(Request $request, $id)
    {
        \Log::info('=== LOGBOOK UPDATE REQUEST ===');
        \Log::info('ID: ' . $id);
        \Log::info('Request data:', $request->all());
        \Log::info('User ID: ' . Auth::id());
        \Log::info('Expects JSON: ' . ($request->expectsJson() ? 'yes' : 'no'));

        try {
            $request->validate([
                'date' => 'required|date',
                'content' => 'required|string',
            ]);

            \Log::info('Validation passed');

            $user = Auth::user();
            $logbook = Logbook::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            \Log::info('Logbook found:', ['id' => $logbook->id, 'date' => $logbook->date, 'user_id' => $logbook->user_id]);

            // Check if another logbook exists for the new date
            $existingLogbook = Logbook::where('user_id', $user->id)
                ->whereDate('date', $request->date)
                ->where('id', '!=', $id)
                ->first();

            if ($existingLogbook) {
                \Log::warning('Duplicate logbook exists for date: ' . $request->date);
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Logbook untuk tanggal ini sudah ada.'
                    ], 422);
                }
                return redirect()->back()
                    ->with('error', 'Logbook untuk tanggal ini sudah ada.');
            }

            \Log::info('Updating logbook with:', ['date' => $request->date, 'content' => substr($request->content, 0, 50) . '...']);

            $logbook->update([
                'date' => $request->date,
                'content' => $request->content,
            ]);

            \Log::info('Logbook updated successfully');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Logbook berhasil diupdate.',
                    'logbook' => $logbook
                ]);
            }

            return redirect()->route('logbook.index')
                ->with('success', 'Logbook berhasil diupdate.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed:', $e->errors());
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Update failed with exception:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }

            throw $e;
        }
    }
    
    /**
     * Delete a logbook entry
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $logbook = Logbook::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        $logbook->delete();
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logbook berhasil dihapus.'
            ]);
        }
        
        return redirect()->route('logbook.index')
            ->with('success', 'Logbook berhasil dihapus.');
    }
    
    /**
     * Display logbook page for mentor
     */
    public function mentorIndex()
    {
        $user = Auth::user();
        
        // Get division mentor
        $divisionMentor = DivisionMentor::where('nik_number', $user->username)->first();
        
        if (!$divisionMentor) {
            return redirect()->route('mentor.dashboard')
                ->with('error', 'Anda tidak memiliki akses untuk melihat logbook.');
        }
        
        // Get participants assigned to this mentor
        $applications = InternshipApplication::where('division_mentor_id', $divisionMentor->id)
            ->where('status', 'accepted')
            ->with(['user'])
            ->get();
        
        $participants = collect();
        foreach ($applications as $app) {
            $logbooks = Logbook::where('user_id', $app->user_id)
                ->orderBy('date', 'desc')
                ->get();
            
            $participants->push([
                'user' => $app->user,
                'logbooks' => $logbooks,
            ]);
        }
        
        return view('logbook.mentor', compact('participants'));
    }
    
    /**
     * Display logbook page for admin
     */
    public function adminIndex(Request $request)
    {
        // Get filter values
        $filterDivision = $request->input('division_id');
        $filterMentor = $request->input('mentor_id');
        
        // Get all divisions
        $divisions = DivisiAdmin::where('is_active', true)
            ->orderBy('division_name')
            ->get();
        
        // Get all mentors (optionally filtered by division)
        $mentorsQuery = DivisionMentor::query();
        if ($filterDivision) {
            $mentorsQuery->where('division_id', $filterDivision);
        }
        $mentors = $mentorsQuery->orderBy('mentor_name')->get();
        
        // Build query for applications
        $query = InternshipApplication::where('status', 'accepted')
            ->with(['user', 'divisionAdmin', 'divisionMentor']);
        
        if ($filterDivision) {
            $query->where('division_admin_id', $filterDivision);
        }
        
        if ($filterMentor) {
            $query->where('division_mentor_id', $filterMentor);
        }
        
        $applications = $query->get();
        
        $participants = collect();
        foreach ($applications as $app) {
            $logbooks = Logbook::where('user_id', $app->user_id)
                ->orderBy('date', 'desc')
                ->get();
            
            $participants->push([
                'user' => $app->user,
                'application' => $app,
                'logbooks' => $logbooks,
            ]);
        }
        
        return view('logbook.admin', compact('participants', 'divisions', 'mentors', 'filterDivision', 'filterMentor'));
    }
    
    /**
     * Get mentors by division (AJAX)
     */
    public function getMentorsByDivision(Request $request)
    {
        $divisionId = $request->input('division_id');
        
        $query = DivisionMentor::query();
        if ($divisionId) {
            $query->where('division_id', $divisionId);
        }
        
        $mentors = $query->orderBy('mentor_name')->get();
        
        return response()->json([
            'mentors' => $mentors
        ]);
    }
}
