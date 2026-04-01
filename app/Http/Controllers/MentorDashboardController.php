<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\InternshipApplication;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MentorDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Cari division_mentor berdasarkan username (nik_number) mentor yang login
        $divisionMentor = \App\Models\DivisionMentor::where('nik_number', $user->username)->first();
        $divisi = $user->divisi;
        
        // Pengajuan pending masih menggunakan divisi (karena belum di-assign ke mentor)
        $pendingApplications = $divisi
            ? $divisi->internshipApplications()->where('status', 'pending')->count()
            : 0;
        
        // Peserta aktif menggunakan division_mentor_id
        $activeParticipants = $divisionMentor
            ? \App\Models\InternshipApplication::where('division_mentor_id', $divisionMentor->id)
                ->where('status', 'accepted')
                ->where(function($q) {
                    $q->whereNull('end_date')->orWhere('end_date', '>=', now());
                })
                ->count()
            : 0;
        
        // Tugas yang perlu dinilai menggunakan division_mentor_id
        $assignmentsToGrade = $divisionMentor
            ? \App\Models\Assignment::whereHas('user.internshipApplications', function($q) use ($divisionMentor) {
                $q->where('division_mentor_id', $divisionMentor->id)->where('status', 'accepted');
            })->whereNotNull('submission_file_path')->whereNull('grade')->count()
            : 0;
        
        $pengajuanBaru = $divisi
            ? $divisi->internshipApplications()->where('status', 'pending')->count()
            : 0;
        
        $tugasBaruDiupload = $divisionMentor
            ? \App\Models\Assignment::whereHas('user.internshipApplications', function($q) use ($divisionMentor) {
                $q->where('division_mentor_id', $divisionMentor->id)->where('status', 'accepted');
            })->whereNotNull('submission_file_path')->whereNull('grade')->count()
            : 0;

        // ========== NEW COMPREHENSIVE STATISTICS ==========

        // Task Statistics
        $totalAssignments = $divisionMentor
            ? \App\Models\Assignment::whereHas('user.internshipApplications', function($q) use ($divisionMentor) {
                $q->where('division_mentor_id', $divisionMentor->id)->where('status', 'accepted');
            })->count()
            : 0;

        $completedAssignments = $divisionMentor
            ? \App\Models\Assignment::whereHas('user.internshipApplications', function($q) use ($divisionMentor) {
                $q->where('division_mentor_id', $divisionMentor->id)->where('status', 'accepted');
            })->whereNotNull('grade')->count()
            : 0;

        // Performance Metrics
        $averageGrade = $divisionMentor
            ? \App\Models\Assignment::whereHas('user.internshipApplications', function($q) use ($divisionMentor) {
                $q->where('division_mentor_id', $divisionMentor->id)->where('status', 'accepted');
            })->whereNotNull('grade')->avg('grade')
            : 0;

        $completionRate = $totalAssignments > 0
            ? round(($completedAssignments / $totalAssignments) * 100, 1)
            : 0;

        // Recent Activity (Last 7 days) - with submissions relationship
        $recentSubmissions = $divisionMentor
            ? \App\Models\Assignment::whereHas('user.internshipApplications', function($q) use ($divisionMentor) {
                $q->where('division_mentor_id', $divisionMentor->id)->where('status', 'accepted');
            })
            ->with(['user', 'submissions' => function($q) {
                $q->orderBy('submitted_at', 'desc')->limit(1);
            }])
            ->whereHas('submissions', function($q) {
                $q->where('submitted_at', '>=', now()->subDays(7));
            })
            ->orderByDesc(function($query) {
                $query->select('submitted_at')
                    ->from('assignment_submissions')
                    ->whereColumn('assignment_id', 'assignments.id')
                    ->orderBy('submitted_at', 'desc')
                    ->limit(1);
            })
            ->limit(10)
            ->get()
            : collect();

        // Today's Attendance Summary
        $todayAttendance = $divisionMentor
            ? \App\Models\Attendance::whereDate('date', today())
                ->whereIn('user_id', function($query) use ($divisionMentor) {
                    $query->select('user_id')
                          ->from('internship_applications')
                          ->where('division_mentor_id', $divisionMentor->id)
                          ->where('status', 'accepted');
                })
                ->get()
            : collect();

        $attendanceStats = [
            'present' => $todayAttendance->where('status', 'Hadir')->count(),
            'late' => $todayAttendance->where('status', 'Terlambat')->count(),
            'absent' => $todayAttendance->where('status', 'Absen')->count(),
        ];

        // Chart Data: Participant Completion Percentage
        $participantCompletionData = ['labels' => [], 'data' => []];
        $completionDistributionData = ['labels' => [], 'data' => []];

        if ($divisionMentor) {
            $acceptedParticipants = \App\Models\InternshipApplication::with('user.assignments')
                ->where('division_mentor_id', $divisionMentor->id)
                ->where('status', 'accepted')
                ->get();

            $completedAllCount = 0;
            $inProgressCount = 0;
            $notStartedCount = 0;
            $noTaskCount = 0;

            foreach ($acceptedParticipants as $participant) {
                $totalTasks = $participant->user->assignments->count();
                $completedTasks = $participant->user->assignments->whereNotNull('grade')->count();
                $completionPercentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

                // Bar chart data
                $participantCompletionData['labels'][] = $participant->user->name ?? 'Unknown';
                $participantCompletionData['data'][] = $completionPercentage;

                // Distribution data
                if ($totalTasks === 0) {
                    $noTaskCount++;
                } elseif ($completedTasks === $totalTasks) {
                    $completedAllCount++;
                } elseif ($completedTasks > 0) {
                    $inProgressCount++;
                } else {
                    $notStartedCount++;
                }
            }

            // Build distribution chart data (only include non-zero categories)
            $distLabels = [];
            $distData = [];
            if ($completedAllCount > 0) { $distLabels[] = 'Selesai Semua'; $distData[] = $completedAllCount; }
            if ($inProgressCount > 0) { $distLabels[] = 'Sedang Mengerjakan'; $distData[] = $inProgressCount; }
            if ($notStartedCount > 0) { $distLabels[] = 'Belum Mulai'; $distData[] = $notStartedCount; }
            if ($noTaskCount > 0) { $distLabels[] = 'Belum Ada Tugas'; $distData[] = $noTaskCount; }

            $completionDistributionData = ['labels' => $distLabels, 'data' => $distData];
        }

        return view('mentor.dashboard', [
            // Existing statistics
            'pendingApplications' => $pendingApplications,
            'activeParticipants' => $activeParticipants,
            'assignmentsToGrade' => $assignmentsToGrade,
            'pengajuanBaru' => $pengajuanBaru,
            'tugasBaruDiupload' => $tugasBaruDiupload,

            // New comprehensive statistics
            'totalAssignments' => $totalAssignments,
            'completedAssignments' => $completedAssignments,
            'averageGrade' => round($averageGrade ?? 0, 1),
            'completionRate' => $completionRate,
            'recentSubmissions' => $recentSubmissions,
            'attendanceStats' => $attendanceStats,

            // New chart data
            'participantCompletionData' => $participantCompletionData,
            'completionDistributionData' => $completionDistributionData,
        ]);
    }

    public function pengajuan()
    {
        $user = Auth::user();
        $divisi = $user->divisi;
        $applications = $divisi ? $divisi->internshipApplications()->with('user')->orderBy('created_at', 'desc')->get() : collect();
        return view('mentor.pengajuan', [
            'applications' => $applications
        ]);
    }

    public function penugasan()
    {
        $user = Auth::user();
        // Cari division_mentor berdasarkan username (nik_number) mentor yang login
        $divisionMentor = \App\Models\DivisionMentor::where('nik_number', $user->username)->first();
        $acceptedParticipants = $divisionMentor
            ? \App\Models\InternshipApplication::with(['user.assignments.submissions' => function($q) {
                $q->orderBy('submitted_at', 'desc');
              }, 'user.assignments' => function($q) {
                $q->orderBy('created_at', 'desc');
              }])
                ->where('division_mentor_id', $divisionMentor->id)
                ->where('status', 'accepted')
                ->get()
            : collect();
        return view('mentor.penugasan', [
            'participants' => $acceptedParticipants
        ]);
    }

    public function sertifikat()
    {
        $user = Auth::user();
        // Cari division_mentor berdasarkan username (nik_number) mentor yang login
        $divisionMentor = \App\Models\DivisionMentor::where('nik_number', $user->username)->first();
        $participants = $divisionMentor
            ? \App\Models\InternshipApplication::with(['user.assignments', 'user.certificates'])
                ->where('division_mentor_id', $divisionMentor->id)
                ->whereIn('status', ['accepted', 'finished'])
                ->get()
            : collect();
        
        // Update otomatis status menjadi finished jika end_date sudah lewat
        $participants->each(function($p) {
            if ($p->status === 'accepted' && $p->end_date && now()->isAfter($p->end_date)) {
                $p->status = 'finished';
                $p->save();
            }
        });
        
        // Tambahkan status selesai magang (hanya berdasarkan end_date)
        $participants = $participants->map(function($p) {
            $assignments = $p->user->assignments;
            $isEndDatePassed = $p->end_date && now()->isAfter($p->end_date);
            $allAssignmentsGraded = $assignments->count() > 0 && $assignments->every(fn($a) => $a->grade !== null);
            $noRevision = $assignments->count() > 0 && $assignments->every(fn($a) => $a->is_revision !== 1);
            // Syarat upload: semua tugas dinilai/feedback dan tidak ada tugas status revisi
            $p->can_upload_certificate = $allAssignmentsGraded && $noRevision;
            $p->is_completed = $isEndDatePassed;
            $p->all_assignments_graded = $allAssignmentsGraded;
            return $p;
        });
        
        return view('mentor.sertifikat', [
            'participants' => $participants
        ]);
    }

    public function profil()
    {
        $user = Auth::user();
        
        // Get division mentor data based on username (NIK)
        $divisionMentor = \App\Models\DivisionMentor::where('nik_number', $user->username)->first();
        $divisionAdmin = $divisionMentor ? $divisionMentor->division : null;
        
        return view('mentor.profil', [
            'user' => $user,
            'divisionMentor' => $divisionMentor,
            'divisionAdmin' => $divisionAdmin
        ]);
    }

    public function updateBiodata(Request $request)
    {
        $request->validateWithBag('biodata', [
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
        ]);

        $user = Auth::user();
        $user->phone = $request->phone;
        $user->email = $request->email;
        $user->save();

        return redirect()->route('mentor.profil')
            ->with('biodata_success', 'Biodata kontak berhasil diperbarui.');
    }

    public function absensi()
    {
        $user = Auth::user();
        // Cari division_mentor berdasarkan username (nik_number) mentor yang login
        $divisionMentor = \App\Models\DivisionMentor::where('nik_number', $user->username)->first();
        $participants = $divisionMentor
            ? \App\Models\InternshipApplication::with(['user'])
                ->where('division_mentor_id', $divisionMentor->id)
                ->where('status', 'accepted')
                ->get()
            : collect();
        
        return view('mentor.absensi', [
            'participants' => $participants
        ]);
    }

    public function laporanPenilaian()
    {
        return view('mentor.laporan-penilaian');
    }

    public function getLaporanPenilaianData(Request $request)
    {
        $user = Auth::user();
        // Cari division_mentor berdasarkan username (nik_number) mentor yang login
        $divisionMentor = \App\Models\DivisionMentor::where('nik_number', $user->username)->first();
        
        if (!$divisionMentor) {
            return response()->json(['data' => []]);
        }

        $year = $request->input('year');
        $month = $request->input('month');
        $now = now();

        $query = \App\Models\InternshipApplication::query()
            ->where('division_mentor_id', $divisionMentor->id)
            ->whereIn('status', ['accepted', 'finished'])
            ->whereNotNull('start_date')
            ->with(['user.assignments', 'user.certificates', 'divisi.subDirektorat.direktorat']);

        // Filter periode
        if ($year && $month) {
            $start = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
            $end = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();
        } elseif ($year) {
            $start = \Carbon\Carbon::create($year, 1, 1)->startOfYear();
            $end = \Carbon\Carbon::create($year, 1, 1)->endOfYear();
        } else {
            // Default: current month
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfMonth();
        }
        
        // Filter berdasarkan overlap periode magang
        $query->where(function($q) use ($start, $end) {
            $q->where(function($sub) use ($start, $end) {
                $sub->whereDate('start_date', '<=', $end->toDateString())
                     ->where(function($sub2) use ($start) {
                         $sub2->whereNull('end_date')
                               ->orWhereDate('end_date', '>=', $start->toDateString());
                     });
            });
        });

        $applications = $query->orderBy('start_date', 'asc')->get();

        // Data peserta dengan status upload laporan
        $peserta = $applications->map(function($app, $i) {
            $user = $app->user;
            
            return [
                'no' => $i+1,
                'id' => $app->id,
                'nama' => $user->name ?? '-',
                'assessment_report_path' => $app->assessment_report_path,
                'has_report' => !empty($app->assessment_report_path),
            ];
        })->toArray();

        return response()->json([
            'data' => $peserta
        ]);
    }

    public function getLaporanPenilaianYears(Request $request)
    {
        $user = Auth::user();
        // Cari division_mentor berdasarkan username (nik_number) mentor yang login
        $divisionMentor = \App\Models\DivisionMentor::where('nik_number', $user->username)->first();
        
        if (!$divisionMentor) {
            return response()->json(['data' => []]);
        }

        $data = [];
        $minDate = \App\Models\InternshipApplication::where('division_mentor_id', $divisionMentor->id)
            ->whereNotNull('start_date')
            ->min('start_date');
        $maxDate = \App\Models\InternshipApplication::where('division_mentor_id', $divisionMentor->id)
            ->whereNotNull('start_date')
            ->max('start_date');
        
        $currentYear = date('Y');
        $minYear = $minDate ? date('Y', strtotime($minDate)) : $currentYear;
        $maxYear = $maxDate ? date('Y', strtotime($maxDate)) : $currentYear;
        
        if ($minYear > $currentYear) {
            $minYear = $currentYear;
        }
        if ($maxYear < $currentYear) {
            $maxYear = $currentYear;
        }

        for ($y = $minYear; $y <= $maxYear; $y++) {
            $data[] = ['value' => $y, 'label' => $y];
        }
        
        return response()->json(['data' => $data]);
    }

    public function getLaporanPenilaianPeriods(Request $request)
    {
        $user = Auth::user();
        // Cari division_mentor berdasarkan username (nik_number) mentor yang login
        $divisionMentor = \App\Models\DivisionMentor::where('nik_number', $user->username)->first();
        
        if (!$divisionMentor) {
            return response()->json(['data' => []]);
        }

        $data = [];
        $minDate = \App\Models\InternshipApplication::where('division_mentor_id', $divisionMentor->id)
            ->whereNotNull('start_date')
            ->min('start_date');
        $maxDate = \App\Models\InternshipApplication::where('division_mentor_id', $divisionMentor->id)
            ->whereNotNull('start_date')
            ->max('start_date');
        
        $currentYear = date('Y');
        $minYear = $minDate ? date('Y', strtotime($minDate)) : $currentYear;
        $maxYear = $maxDate ? date('Y', strtotime($maxDate)) : $currentYear;
        
        if ($minYear > $currentYear) {
            $minYear = $currentYear;
        }
        if ($maxYear < $currentYear) {
            $maxYear = $currentYear;
        }

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        for ($y = $minYear; $y <= $maxYear; $y++) {
            foreach ($months as $num => $name) {
                $data[] = [ 'value' => sprintf('%02d', $num).'-'.$y, 'label' => $name.' '.$y ];
            }
        }
        
        return response()->json(['data' => $data]);
    }

    public function uploadLaporanPenilaian(Request $request, $applicationId)
    {
        $request->validate([
            'assessment_report' => 'required|file|mimes:pdf|max:10240',
        ]);

        $application = \App\Models\InternshipApplication::findOrFail($applicationId);
        $user = Auth::user();
        
        // Pastikan hanya mentor divisi terkait yang bisa upload
        if ($user->divisi_id !== $application->divisi_id) {
            abort(403);
        }

        // Hapus file lama jika ada
        if ($application->assessment_report_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($application->assessment_report_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($application->assessment_report_path);
        }

        // Simpan file baru
        $path = $request->file('assessment_report')->store('assessment_reports', 'public');
        $application->assessment_report_path = $path;
        $application->save();

        return response()->json([
            'success' => true,
            'message' => 'Laporan penilaian berhasil diupload.',
            'path' => $path
        ]);
    }

    public function downloadLaporanPenilaian($applicationId)
    {
        $application = \App\Models\InternshipApplication::findOrFail($applicationId);
        $user = Auth::user();
        
        // Pastikan hanya mentor divisi terkait yang bisa download
        if ($user->divisi_id !== $application->divisi_id) {
            abort(403);
        }

        if (!$application->assessment_report_path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($application->assessment_report_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download(
            $application->assessment_report_path,
            'Laporan_Penilaian_' . $application->user->name . '.pdf'
        );
    }

    public function deleteLaporanPenilaian($applicationId)
    {
        $application = \App\Models\InternshipApplication::findOrFail($applicationId);
        $user = Auth::user();
        
        // Pastikan hanya mentor divisi terkait yang bisa delete
        if ($user->divisi_id !== $application->divisi_id) {
            abort(403);
        }

        if ($application->assessment_report_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($application->assessment_report_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($application->assessment_report_path);
        }

        $application->assessment_report_path = null;
        $application->save();

        return response()->json([
            'success' => true,
            'message' => 'Laporan penilaian berhasil dihapus.'
        ]);
    }

    public function responPengajuan(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:accepted,rejected,postponed',
            'notes' => 'required_if:status,rejected,postponed',
        ], [
            'notes.required_if' => 'Alasan wajib diisi untuk penolakan atau penundaan.'
        ]);

        $application = \App\Models\InternshipApplication::findOrFail($id);
        // Pastikan hanya pembimbing divisi terkait yang bisa merespon
        if (Auth::user()->divisi_id !== $application->divisi_id) {
            abort(403);
        }
        $application->status = $request->status;
        $application->notes = $request->status === 'accepted' ? null : $request->notes;
        $application->save();

        // Set divisi_id user jika diterima
        if ($request->status === 'accepted') {
            $application->user->divisi_id = $application->divisi_id;
            $application->user->save();
        }

        return redirect()->route('mentor.pengajuan')->with('success', 'Respon pengajuan berhasil disimpan.');
    }

    public function tambahPenugasan(Request $request)
    {
        // Validasi dasar terlebih dahulu - support multiple user_ids
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'assignment_type' => 'required|in:tugas_harian,tugas_proyek',
            'deadline' => 'required|date',
            'presentation_date' => 'nullable|date',
            'description' => 'nullable|string|max:5000',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx,zip|max:4096',
        ]);
        
        // Validasi deadline manual untuk fleksibilitas lebih baik
        $deadline = \Carbon\Carbon::parse($request->deadline);
        if ($deadline->lt(now()->startOfDay())) {
            return redirect()->back()
                ->withErrors(['deadline' => 'Deadline harus hari ini atau setelahnya.'])
                ->withInput($request->except('file_path'));
        }
        
        // Validasi presentation_date jika diisi
        if ($request->presentation_date) {
            $presentationDate = \Carbon\Carbon::parse($request->presentation_date);
            if ($presentationDate->lt(now()->startOfDay())) {
                return redirect()->back()
                    ->withErrors(['presentation_date' => 'Tanggal presentasi harus hari ini atau setelahnya.'])
                    ->withInput($request->except('file_path'));
            }
        }
        
        // Validasi khusus untuk tugas proyek
        if ($request->assignment_type === 'tugas_proyek' && !$request->presentation_date) {
            return redirect()->back()
                ->withErrors(['presentation_date' => 'Tanggal presentasi wajib diisi untuk tugas proyek.'])
                ->withInput($request->except('file_path'));
        }
        
        $user = Auth::user();

        // Cari division_mentor dengan caching untuk performa lebih baik
        $divisionMentor = \App\Models\DivisionMentor::where('nik_number', $user->username)->first();

        if (!$divisionMentor) {
            return redirect()->back()
                ->with('error', 'Anda tidak memiliki akses untuk membuat penugasan.')
                ->withInput($request->except('file_path'));
        }

        // Handle file upload sekali saja jika ada
        $filePath = null;
        if ($request->hasFile('file_path')) {
            try {
                $filePath = $request->file('file_path')->store('assignments', 'public');
            } catch (\Exception $e) {
                Log::error('Error uploading file: ' . $e->getMessage());
                return redirect()->back()
                    ->with('error', 'Gagal mengupload file. Pastikan file tidak terlalu besar dan format sesuai.')
                    ->withInput($request->except('file_path'));
            }
        }

        // Loop through each selected user and create assignment
        $successCount = 0;
        $errorUsers = [];

        foreach ($request->user_ids as $userId) {
            // Optimasi query dengan select hanya field yang diperlukan
            $application = \App\Models\InternshipApplication::select('id', 'user_id', 'status', 'start_date', 'division_mentor_id')
                ->where('user_id', $userId)
                ->where('status', 'accepted')
                ->where('division_mentor_id', $divisionMentor->id)
                ->orderBy('start_date', 'desc')
                ->first();

            if (!$application) {
                $errorUsers[] = $userId;
                continue;
            }

            // Cek apakah peserta sudah mulai magang
            if ($application->start_date) {
                $startDate = \Carbon\Carbon::parse($application->start_date);
                if ($startDate->gt(now())) {
                    $errorUsers[] = $userId;
                    continue;
                }
            }

            // Siapkan data untuk assignment
            $data = [
                'user_id' => $userId,
                'title' => trim($request->title),
                'assignment_type' => $request->assignment_type,
                'description' => $request->description ? trim($request->description) : null,
                'deadline' => $request->deadline,
                'presentation_date' => ($request->assignment_type === 'tugas_proyek' && $request->presentation_date)
                    ? $request->presentation_date
                    : null,
                'file_path' => $filePath,
            ];

            // Buat assignment
            try {
                $assignment = \App\Models\Assignment::create($data);
                
                // Buat notifikasi untuk peserta
                try {
                    $user = \App\Models\User::find($userId);
                    if ($user) {
                        $notification = \App\Services\NotificationService::assignmentCreated($user, $assignment);
                        Log::info('Notification created for user ' . $userId . ', notification ID: ' . $notification->id);
                    } else {
                        Log::warning('User not found for notification: ' . $userId);
                    }
                } catch (\Exception $notifError) {
                    Log::error('Error creating notification for assignment: ' . $notifError->getMessage());
                    Log::error('Stack trace: ' . $notifError->getTraceAsString());
                }
                
                $successCount++;
            } catch (\Exception $e) {
                Log::error('Error creating assignment for user ' . $userId . ': ' . $e->getMessage());
                $errorUsers[] = $userId;
            }
        }

        // Generate response message
        $message = "Berhasil membuat {$successCount} penugasan.";
        if (!empty($errorUsers)) {
            $message .= " Gagal untuk " . count($errorUsers) . " peserta.";
        }

        return redirect()->route('mentor.penugasan')
            ->with('success', $message);
    }

    public function beriNilaiPenugasan(Request $request, $assignmentId)
    {
        $assignment = \App\Models\Assignment::findOrFail($assignmentId);
        $user = Auth::user();
        // Cari division_mentor berdasarkan username (nik_number) mentor yang login
        $divisionMentor = \App\Models\DivisionMentor::where('nik_number', $user->username)->first();
        // Validasi: pastikan assignment milik peserta yang di-assign ke mentor ini
        $application = $assignment->user ? $assignment->user->internshipApplications()->where('status', 'accepted')->first() : null;
        if (!$application || !$divisionMentor || $application->division_mentor_id !== $divisionMentor->id) {
            abort(403);
        }
        // Jika revisi diizinkan, hanya feedback yang bisa diinput
        if ($assignment->is_revision === 1) {
            $request->validate([
                'feedback' => 'required|string',
            ]);
            $assignment->feedback = $request->feedback;
            \App\Models\AssignmentFeedbackLog::create([
                'assignment_id' => $assignment->id,
                'mentor_user_id' => $user->id,
                'feedback' => $request->feedback,
            ]);
            // Nilai tidak diubah
        } else {
            $request->validate([
                'grade' => 'required|numeric|min:0|max:100',
                'feedback' => 'nullable|string',
            ]);
            $assignment->grade = $request->grade;
            $assignment->feedback = $request->feedback;
        }
        $assignment->save();
        return redirect()->route('mentor.penugasan')
            ->with('success', 'Penilaian tugas berhasil disimpan.')
            ->with('feedback_saved_assignment_id', $assignment->id);
    }

    public function setRevisiPenugasan(Request $request, $assignmentId)
    {
        $request->validate([
            'is_revision' => 'required|in:0,1',
            'feedback' => 'nullable|string',
        ]);
        $assignment = \App\Models\Assignment::findOrFail($assignmentId);
        $user = Auth::user();
        // Cari division_mentor berdasarkan username (nik_number) mentor yang login
        $divisionMentor = \App\Models\DivisionMentor::where('nik_number', $user->username)->first();
        // Validasi: pastikan assignment milik peserta yang di-assign ke mentor ini
        $application = $assignment->user ? $assignment->user->internshipApplications()->where('status', 'accepted')->first() : null;
        if (!$application || !$divisionMentor || $application->division_mentor_id !== $divisionMentor->id) {
            abort(403);
        }
        $assignment->is_revision = (int) $request->is_revision;
        // Jika tugas ditandai sebagai revisi, hapus nilai agar tidak bisa dinilai bersamaan
        if ((int) $request->is_revision === 1) {
            $assignment->grade = null;
        }

        // Support flow: isi feedback dulu lalu klik "Revisi"
        if ((int) $request->is_revision === 1 && $request->filled('feedback')) {
            $assignment->feedback = $request->feedback;
            \App\Models\AssignmentFeedbackLog::create([
                'assignment_id' => $assignment->id,
                'mentor_user_id' => $user->id,
                'feedback' => $request->feedback,
            ]);
        }
        $assignment->save();

        return redirect()->route('mentor.penugasan')
            ->with('success', 'Status revisi ditetapkan. Silakan isi feedback lalu simpan penilaian.')
            ->with('revision_set_assignment_id', $assignment->id);
    }

    public function editPenugasan($assignmentId)
    {
        $user = Auth::user();
        $divisionMentor = \App\Models\DivisionMentor::where('nik_number', $user->username)->first();

        if (!$divisionMentor) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit penugasan.');
        }

        $assignment = \App\Models\Assignment::with('user')->findOrFail($assignmentId);

        // Validasi: pastikan assignment milik peserta yang di-assign ke mentor ini
        $application = $assignment->user ? $assignment->user->internshipApplications()
            ->where('status', 'accepted')
            ->where('division_mentor_id', $divisionMentor->id)
            ->first() : null;

        if (!$application) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit penugasan ini.');
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'description' => $assignment->description,
                'assignment_type' => $assignment->assignment_type,
                'deadline' => $assignment->deadline,
                'presentation_date' => $assignment->presentation_date,
                'file_path' => $assignment->file_path,
            ]
        ]);
    }

    public function updatePenugasan(Request $request, $assignmentId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'assignment_type' => 'required|in:tugas_harian,tugas_proyek',
            'deadline' => 'required|date',
            'presentation_date' => 'nullable|date',
            'description' => 'nullable|string|max:5000',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx,zip|max:4096',
        ]);

        $user = Auth::user();
        $divisionMentor = \App\Models\DivisionMentor::where('nik_number', $user->username)->first();

        if (!$divisionMentor) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengupdate penugasan.');
        }

        $assignment = \App\Models\Assignment::with('user')->findOrFail($assignmentId);

        // Validasi: pastikan assignment milik peserta yang di-assign ke mentor ini
        $application = $assignment->user ? $assignment->user->internshipApplications()
            ->where('status', 'accepted')
            ->where('division_mentor_id', $divisionMentor->id)
            ->first() : null;

        if (!$application) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengupdate penugasan ini.');
        }

        // Validasi deadline
        $deadline = \Carbon\Carbon::parse($request->deadline);
        if ($deadline->lt(now()->startOfDay())) {
            return redirect()->back()
                ->withErrors(['deadline' => 'Deadline harus hari ini atau setelahnya.'])
                ->withInput($request->except('file_path'));
        }

        // Validasi presentation_date jika diisi
        if ($request->presentation_date) {
            $presentationDate = \Carbon\Carbon::parse($request->presentation_date);
            if ($presentationDate->lt(now()->startOfDay())) {
                return redirect()->back()
                    ->withErrors(['presentation_date' => 'Tanggal presentasi harus hari ini atau setelahnya.'])
                    ->withInput($request->except('file_path'));
            }
        }

        // Validasi khusus untuk tugas proyek
        if ($request->assignment_type === 'tugas_proyek' && !$request->presentation_date) {
            return redirect()->back()
                ->withErrors(['presentation_date' => 'Tanggal presentasi wajib diisi untuk tugas proyek.'])
                ->withInput($request->except('file_path'));
        }

        // Update data
        $assignment->title = trim($request->title);
        $assignment->assignment_type = $request->assignment_type;
        $assignment->description = $request->description ? trim($request->description) : null;
        $assignment->deadline = $request->deadline;
        $assignment->presentation_date = ($request->assignment_type === 'tugas_proyek' && $request->presentation_date)
            ? $request->presentation_date
            : null;

        // Handle file upload jika ada
        if ($request->hasFile('file_path')) {
            // Hapus file lama jika ada
            if ($assignment->file_path && Storage::disk('public')->exists($assignment->file_path)) {
                Storage::disk('public')->delete($assignment->file_path);
            }

            try {
                $assignment->file_path = $request->file('file_path')->store('assignments', 'public');
            } catch (\Exception $e) {
                Log::error('Error uploading file: ' . $e->getMessage());
                return redirect()->back()
                    ->with('error', 'Gagal mengupload file. Pastikan file tidak terlalu besar dan format sesuai.')
                    ->withInput($request->except('file_path'));
            }
        }

        try {
            $assignment->save();
            return redirect()->route('mentor.penugasan')
                ->with('success', 'Penugasan berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error updating assignment: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengupdate penugasan. Silakan coba lagi.')
                ->withInput($request->except('file_path'));
        }
    }

    public function deletePenugasan($assignmentId)
    {
        $user = Auth::user();
        $divisionMentor = \App\Models\DivisionMentor::where('nik_number', $user->username)->first();

        if (!$divisionMentor) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menghapus penugasan.');
        }

        $assignment = \App\Models\Assignment::with('user')->findOrFail($assignmentId);

        // Validasi: pastikan assignment milik peserta yang di-assign ke mentor ini
        $application = $assignment->user ? $assignment->user->internshipApplications()
            ->where('status', 'accepted')
            ->where('division_mentor_id', $divisionMentor->id)
            ->first() : null;

        if (!$application) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menghapus penugasan ini.');
        }

        // Cek apakah sudah ada submission
        if ($assignment->submission_file_path || ($assignment->submissions && $assignment->submissions->count() > 0)) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus tugas yang sudah dikumpulkan oleh peserta.');
        }

        try {
            // Hapus file assignment jika ada
            if ($assignment->file_path && Storage::disk('public')->exists($assignment->file_path)) {
                Storage::disk('public')->delete($assignment->file_path);
            }

            $assignment->delete();

            return redirect()->route('mentor.penugasan')
                ->with('success', 'Penugasan berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error deleting assignment: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus penugasan. Silakan coba lagi.');
        }
    }

    public function uploadSertifikat(Request $request, $userId)
    {
        $request->validate([
            'certificate' => 'required|file|mimes:pdf|max:4096',
        ]);
        $user = \App\Models\User::findOrFail($userId);
        // Simpan file
        $path = $request->file('certificate')->store('certificates', 'public');
        // Update/insert certificate
        $certificate = $user->certificates->first();
        if ($certificate) {
            $certificate->certificate_path = $path;
            $certificate->issued_at = now();
            $certificate->save();
        } else {
            $user->certificates()->create([
                'certificate_path' => $path,
                'issued_at' => now(),
            ]);
        }
        return redirect()->route('mentor.sertifikat')->with('success', 'Sertifikat berhasil diupload.');
    }

    public function showAcceptanceLetterForm($id)
    {
        $application = InternshipApplication::with(['user', 'divisi.subDirektorat.direktorat'])->findOrFail($id);
        // Pastikan hanya mentor divisi terkait yang bisa akses
        if (Auth::user()->divisi_id !== $application->divisi_id) {
            abort(403);
        }
        // Hanya bisa jika sudah ada surat pengantar dan belum ada surat penerimaan
        if (!$application->cover_letter_path || $application->acceptance_letter_path) {
            return redirect()->route('mentor.pengajuan')->with('error', 'Surat Penerimaan hanya bisa dikirim jika Surat Pengantar sudah diupload dan belum pernah dikirim.');
        }
        return view('mentor.acceptance_letter_form', compact('application'));
    }

    public function previewAcceptanceLetter(Request $request, $id)
    {
        $application = InternshipApplication::with(['user', 'divisi.subDirektorat.direktorat'])->findOrFail($id);
        if (Auth::user()->divisi_id !== $application->divisi_id) {
            abort(403);
        }
        $data = $this->getAcceptanceLetterData($request, $application);
        $pdf = Pdf::loadView('surat.surat_penerimaan', $data)->setPaper('A4', 'portrait');
        return $pdf->stream('Surat_Penerimaan.pdf');
    }

    public function sendAcceptanceLetter(Request $request, $id)
    {
        $application = InternshipApplication::with(['user', 'divisi.subDirektorat.direktorat'])->findOrFail($id);
        if (Auth::user()->divisi_id !== $application->divisi_id) {
            abort(403);
        }
        if ($application->acceptance_letter_path) {
            return redirect()->route('mentor.pengajuan')->with('error', 'Surat Penerimaan sudah pernah dikirim.');
        }
        $data = $this->getAcceptanceLetterData($request, $application);
        $pdf = Pdf::loadView('surat.surat_penerimaan', $data)->setPaper('A4', 'portrait');
        $filename = 'surat_penerimaan_' . $application->id . '_' . time() . '.pdf';
        $path = 'acceptance_letters/' . $filename;
        Storage::disk('public')->put($path, $pdf->output());
        $application->acceptance_letter_path = $path;
        $application->save();
        return redirect()->route('mentor.pengajuan')->with('success', 'Surat Penerimaan berhasil dikirim dan dapat diunduh oleh peserta.');
    }

    public function showCertificateForm($userId)
    {
        $user = \App\Models\User::with(['certificates', 'internshipApplications' => function($q) {
            $q->whereIn('status', ['accepted', 'finished']);
        }, 'divisi'])->findOrFail($userId);
        $application = $user->internshipApplications->sortByDesc('end_date')->first();
        if (!$application) abort(404);
        return view('mentor.certificate_form', compact('user', 'application'));
    }

    public function previewCertificate(Request $request, $userId)
    {
        $user = \App\Models\User::with(['certificates', 'internshipApplications' => function($q) {
            $q->whereIn('status', ['accepted', 'finished']);
        }, 'divisi'])->findOrFail($userId);
        $application = $user->internshipApplications->sortByDesc('end_date')->first();
        if (!$application) abort(404);
        $data = $this->getCertificateData($request, $user, $application);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('surat.sertifikat', $data)->setPaper('A4', 'landscape');
        return $pdf->stream('Sertifikat.pdf');
    }

    public function sendCertificate(Request $request, $userId)
    {
        $user = \App\Models\User::with(['certificates', 'internshipApplications' => function($q) {
            $q->whereIn('status', ['accepted', 'finished']);
        }, 'divisi'])->findOrFail($userId);
        $application = $user->internshipApplications->sortByDesc('end_date')->first();
        if (!$application) abort(404);
        $data = $this->getCertificateData($request, $user, $application);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('surat.sertifikat', $data)->setPaper('A4', 'landscape');
        $filename = 'sertifikat_' . $user->id . '_' . time() . '.pdf';
        $path = 'certificates/' . $filename;
        Storage::disk('public')->put($path, $pdf->output());
        $user->certificates()->create([
            'certificate_path' => $path,
            'issued_at' => now(),
            'nomor_sertifikat' => $request->input('nomor_sertifikat'),
            'predikat' => $request->input('predikat'),
        ]);
        return redirect()->route('mentor.sertifikat')->with('success', 'Sertifikat berhasil dikirim dan dapat diunduh oleh peserta.');
    }

    private function getAcceptanceLetterData(Request $request, $application)
    {
        $user = $application->user;
        $divisi = $application->divisi;
        $subdirektorat = $divisi->subDirektorat;
        $direktorat = $subdirektorat->direktorat;
        $qrData = json_encode([
            'type' => 'internship_data',
            'nama' => $user->name,
            'nim' => $user->nim,
            'universitas' => $user->university,
            'jurusan' => $user->major,
            'divisi' => $divisi->name,
            'tanggal_mulai' => $application->start_date,
            'tanggal_selesai' => $application->end_date,
            'ktm' => $user->ktm,
        ]);
        
        // Format data dengan prefix yang menghindari interpretasi sebagai nomor telepon
        $qrText = "PESERTA MAGANG PT POS INDONESIA\n\nNama: " . $user->name . "\nID Mahasiswa: " . $user->nim . "\nUniversitas: " . $user->university . "\nDivisi: " . $divisi->name . "\n\nData ini valid dan dapat diverifikasi.";
        $qrSvg = QrCode::format('svg')->size(400)->margin(10)->backgroundColor(0, 0, 0, 0)->generate($qrText);
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
        return [
            'nomor_surat_penerimaan' => $request->input('nomor_surat_penerimaan'),
            'nomor_surat_pengantar' => $request->input('nomor_surat_pengantar'),
            'tanggal_surat_pengantar' => \Carbon\Carbon::parse($request->input('tanggal_surat_pengantar'))->locale('id')->isoFormat('D MMMM Y'),
            'tujuan_surat' => $request->input('tujuan_surat'),
            'tanggal_surat' => now()->locale('id')->isoFormat('D MMMM Y'),
            'asal_surat' => $user->university,
            'divisi_mengeluarkan_surat' => $divisi->name,
            'nama_peserta' => $user->name,
            'nim' => $user->nim,
            'jurusan' => $user->major,
            'jabatan' => $divisi->vp ? 'VP ' . str_replace('Divisi ', '', $divisi->name) : '',
            'nama_pic' => $divisi->vp,
            'nippos' => $divisi->nippos,
            'start_date' => \Carbon\Carbon::parse($application->start_date)->locale('id')->isoFormat('D MMMM Y'),
            'end_date' => \Carbon\Carbon::parse($application->end_date)->locale('id')->isoFormat('D MMMM Y'),
            'ktm' => $user->ktm,
            'qr_base64' => $qrBase64,
        ];
    }

    private function getCertificateData(Request $request, $user, $application)
    {
        // Generate QR Code for certificate
        $qrData = json_encode([
            'type' => 'certificate_data',
            'nama' => $user->name,
            'nim' => $user->nim,
            'universitas' => $user->university,
            'jurusan' => $user->major,
            'divisi' => $user->divisi ? $user->divisi->name : '',
            'tanggal_mulai' => $application->start_date,
            'tanggal_selesai' => $application->end_date,
            'predikat' => $request->input('predikat'),
            'ktm' => $user->ktm,
        ]);
        
        // Format data dengan prefix yang menghindari interpretasi sebagai nomor telepon
        $qrText = "SERTIFIKAT MAGANG PT POS INDONESIA\n\nNama: " . $user->name . "\nID Mahasiswa: " . $user->nim . "\nUniversitas: " . $user->university . "\nDivisi: " . ($user->divisi ? $user->divisi->name : '') . "\nPredikat: " . $request->input('predikat') . "\n\nSertifikat ini valid dan dapat diverifikasi.";
        $qrSvg = QrCode::format('svg')->size(400)->margin(10)->backgroundColor(0, 0, 0, 0)->generate($qrText);
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
        
        return [
            'nomor_sertifikat' => $request->input('nomor_sertifikat'),
            'predikat' => $request->input('predikat'),
            'nama' => $user->name,
            'universitas' => $user->university,
            'jurusan' => $user->major,
            'nim' => $user->nim,
            'start_date' => \Carbon\Carbon::parse($application->start_date)->locale('id')->isoFormat('D MMMM Y'),
            'end_date' => \Carbon\Carbon::parse($application->end_date)->locale('id')->isoFormat('D MMMM Y'),
            'nama_pic' => $user->divisi ? $user->divisi->vp : '',
            'nippos' => $user->divisi ? $user->divisi->nippos : '',
            'jabatan' => $user->divisi ? 'VP ' . str_replace('Divisi ', '', $user->divisi->name) : '',
            'tanggal_sertifikat' => now()->locale('id')->isoFormat('D MMMM Y'),
            'qr_base64' => $qrBase64,
        ];
    }
} 