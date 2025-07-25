<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportsCertificateController extends Controller
{
    /**
     * Display a listing of certificates for reporting.
     */
    public function index(Request $request)
    {
        // Get events for filter dropdown based on user role
        if (auth()->user()->hasRole('Administrator')) {
            $events = Event::orderBy('start_date')->get(['id', 'name']);
        } else {
            $events = Event::where('user_id', auth()->id())->orderBy('start_date')->get(['id', 'name']);
        }
        
        $templates = CertificateTemplate::all(['id', 'name']);
        
        // Query to fetch certificates with filters
        $query = Certificate::with(['event', 'participant', 'template']);
        
        // Filter by user role - administrators see all, organizers see only their events' certificates
        if (!auth()->user()->hasRole('Administrator')) {
            $query->whereHas('event', function($q) {
                $q->where('user_id', auth()->id());
            });
        }
        
        // Apply filters
        if ($request->has('event_filter') && $request->event_filter) {
            $query->where('event_id', $request->event_filter);
        }
        
        if ($request->has('template_filter') && $request->template_filter) {
            $query->where('template_id', $request->template_filter);
        }
        
        if ($request->has('date_range')) {
            $dateRange = $request->date_range;
            if (!empty($dateRange)) {
                $dates = explode(' - ', $dateRange);
                if (count($dates) === 2) {
                    $query->whereBetween('generated_at', [$dates[0] . ' 00:00:00', $dates[1] . ' 23:59:59']);
                }
            }
        }
        
        // Get paginated results
        $certificates = $query->orderBy('generated_at', 'desc')->paginate(10);
        
        // Get summary statistics based on user role
        if (auth()->user()->hasRole('Administrator')) {
            $totalCertificates = Certificate::count();
            $totalTemplates = CertificateTemplate::count();
            $newTemplatesThisMonth = CertificateTemplate::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
        } else {
            // For organizers, only count certificates from their events
            $userEventIds = Event::where('user_id', auth()->id())->pluck('id')->toArray();
            $totalCertificates = Certificate::whereIn('event_id', $userEventIds)->count();
            
            // Templates might be shared across organizers, so we'll count templates used in their events
            $templateIds = Certificate::whereIn('event_id', $userEventIds)
                ->distinct('template_id')
                ->pluck('template_id')
                ->toArray();
            $totalTemplates = count($templateIds);
            
            $newTemplatesThisMonth = CertificateTemplate::whereIn('id', $templateIds)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
        }
        
        // Calculate email delivery rate (placeholder - in a real app this would be tracked)
        $emailDeliveryRate = 98.5; // Placeholder value
        $emailsDelivered = round(($totalCertificates * $emailDeliveryRate) / 100);
        
        return view('reports.certificates', compact(
            'events',
            'templates',
            'certificates',
            'totalCertificates',
            'totalTemplates',
            'newTemplatesThisMonth',
            'emailDeliveryRate',
            'emailsDelivered'
        ));
    }
    
    /**
     * Display the specified certificate.
     */
    public function show($id)
    {
        $certificate = Certificate::with(['event', 'participant', 'template', 'generator'])
            ->where('certificate_number', $id)
            ->orWhere('id', $id)
            ->firstOrFail();
        
        // Check if user has permission to view this certificate
        if (!auth()->user()->hasRole('Administrator')) {
            $event = $certificate->event;
            if (!$event || $event->user_id != auth()->id()) {
                return redirect()->route('reports.certificates')
                    ->with('error', 'You do not have permission to view this certificate.');
            }
        }
        
        return view('reports.certificates-show', compact('certificate'));
    }
    
    /**
     * Download the certificate PDF.
     */
    public function download($id)
    {
        $certificate = Certificate::with('event')
            ->where('certificate_number', $id)
            ->orWhere('id', $id)
            ->firstOrFail();
        
        // Check if user has permission to download this certificate
        if (!auth()->user()->hasRole('Administrator')) {
            $event = $certificate->event;
            if (!$event || $event->user_id != auth()->id()) {
                return redirect()->route('reports.certificates')
                    ->with('error', 'You do not have permission to download this certificate.');
            }
        }
        
        if (!$certificate->pdf_file || !Storage::disk('public')->exists($certificate->pdf_file)) {
            return back()->with('error', 'Certificate PDF file not found.');
        }
        
        $filePath = storage_path('app/public/' . $certificate->pdf_file);
        $fileName = 'Certificate_' . $certificate->certificate_number . '.pdf';
        
        return response()->download($filePath, $fileName);
    }
    
    /**
     * Delete the certificate.
     */
    public function destroy($id)
    {
        $certificate = Certificate::with('event')
            ->where('certificate_number', $id)
            ->orWhere('id', $id)
            ->firstOrFail();
        
        // Check if user has permission to delete this certificate
        if (!auth()->user()->hasRole('Administrator')) {
            $event = $certificate->event;
            if (!$event || $event->user_id != auth()->id()) {
                return redirect()->route('reports.certificates')
                    ->with('error', 'You do not have permission to delete this certificate.');
            }
        }
        
        // Delete PDF file if exists
        if ($certificate->pdf_file && Storage::disk('public')->exists($certificate->pdf_file)) {
            Storage::disk('public')->delete($certificate->pdf_file);
        }
        
        $certificate->delete();
        
        return redirect()->route('reports.certificates')->with('success', 'Certificate deleted successfully.');
    }
} 