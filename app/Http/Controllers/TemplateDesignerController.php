<?php

namespace App\Http\Controllers;

use App\Models\CertificateTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema; // Added this import for Schema

class TemplateDesignerController extends Controller
{
    /**
     * Display a listing of the templates.
     */
    public function index(Request $request)
    {
        $query = CertificateTemplate::query();

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter by orientation
        if ($request->filled('orientation')) {
            $query->where('orientation', $request->orientation);
        }

        // Filter by date range
        if ($request->filled('date_filter')) {
            $today = now()->startOfDay();
            switch ($request->date_filter) {
                case 'today':
                    $query->whereDate('created_at', $today->format('Y-m-d'));
                    break;
                case 'week':
                    $query->whereBetween('created_at', [$today->format('Y-m-d'), $today->addDays(7)->format('Y-m-d')]);
                    break;
                case 'month':
                    $query->whereBetween('created_at', [$today->format('Y-m-d'), $today->addMonth()->format('Y-m-d')]);
                    break;
                case 'past':
                    $query->where('created_at', '<', $today->format('Y-m-d'));
                    break;
            }
        }

        // Get paginated results with per_page parameter
        $perPage = $request->get('per_page', 10);
        $templates = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        return view('templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new template.
     */
    public function create()
    {
        return view('templates.create');
    }

    /**
     * Show the designer page for creating a new template.
     */
    public function designer(Request $request, $id = null)
    {
        $template = null;
        $templateLibraries = [];
        $categories = ['landscape', 'portrait']; // Basic categories
        
        if ($id) {
            $template = CertificateTemplate::findOrFail($id);
        }
        
        return view('templates.designer', compact('template', 'templateLibraries', 'categories'));
    }

    /**
     * Store a newly created template in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pdf_file' => 'required_without:background_pdf|file|mimes:pdf|max:10240', // 10MB max
            'background_pdf' => 'nullable|string',
            'orientation' => 'required|in:portrait,landscape',
            'template_data' => 'nullable|string',
        ]);

        // Log request data untuk debugging
        \Log::info('Template store request data:', [
            'has_template_data' => $request->has('template_data'),
            'template_data_length' => $request->has('template_data') ? strlen($request->template_data) : 0,
        ]);

        $templateData = $request->template_data;
        if (is_string($templateData)) {
            $templateData = json_decode($templateData, true);
            
            // Log decoded template data
            \Log::info('Template data decoded:', [
                'elements_count' => isset($templateData['elements']) ? count($templateData['elements']) : 0,
                'template_data' => $templateData
            ]);
        }

        $pdfPath = null;
        $backgroundPdf = null;

        // Check if we have a PDF file uploaded
        if ($request->hasFile('pdf_file')) {
            $pdfPath = $request->file('pdf_file')->store('certificate-templates', 'public');
            $backgroundPdf = Storage::url($pdfPath);
        } 
        // If no file but background_pdf is provided (URL or existing file)
        elseif ($request->has('background_pdf') && $request->background_pdf) {
            $backgroundPdf = $request->background_pdf;
            // If the background_pdf is a file URL, set as pdf_file too
            if (strpos($backgroundPdf, '/storage/') !== false) {
                $pdfPath = str_replace('/storage/', '', $backgroundPdf);
            }
        }

        // Prepare data for creating template
        $templateData = [
            'name' => $request->name,
            'description' => $request->description,
            'pdf_file' => $pdfPath,
            'background_pdf' => $backgroundPdf,
            'orientation' => $request->orientation,
            'template_data' => $templateData,
            'placeholders' => [], // Keep the old placeholders field empty
            'created_by' => auth()->id(),
        ];

        // Check if is_active column exists and add it to template data if it does
        if (Schema::hasColumn('certificate_templates', 'is_active')) {
            $templateData['is_active'] = $request->has('is_active') ? $request->is_active : true;
        }

        // Create the template
        $template = CertificateTemplate::create($templateData);

        return redirect()->route('template.designer')
            ->with('success', 'Template created successfully.');
    }

    /**
     * Display the specified template.
     */
    public function show($id)
    {
        $template = CertificateTemplate::findOrFail($id);
        return view('templates.show', compact('template'));
    }

    /**
     * Show the form for editing the specified template.
     */
    public function edit($id)
    {
        $template = CertificateTemplate::findOrFail($id);
        return view('templates.edit', compact('template'));
    }

    /**
     * Update the specified template in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pdf_file' => 'nullable|file|mimes:pdf|max:10240', // 10MB max
            'background_pdf' => 'nullable|string',
            'orientation' => 'required|in:portrait,landscape',
            'template_data' => 'nullable|string',
        ]);

        // Log request data untuk debugging
        \Log::info('Template update request data:', [
            'template_id' => $id,
            'has_template_data' => $request->has('template_data'),
            'template_data_length' => $request->has('template_data') ? strlen($request->template_data) : 0,
        ]);

        $template = CertificateTemplate::findOrFail($id);

        $templateData = $request->template_data;
        if (is_string($templateData)) {
            $templateData = json_decode($templateData, true);
            
            // Log decoded template data
            \Log::info('Template data decoded for update:', [
                'elements_count' => isset($templateData['elements']) ? count($templateData['elements']) : 0,
                'template_data' => $templateData
            ]);
        }

        // Update PDF file if provided
        if ($request->hasFile('pdf_file')) {
            // Delete old file if it exists
            if ($template->pdf_file) {
                Storage::disk('public')->delete($template->pdf_file);
            }
            
            // Store new file
            $pdfPath = $request->file('pdf_file')->store('certificate-templates', 'public');
            $template->pdf_file = $pdfPath;
            $template->background_pdf = Storage::url($pdfPath);
        }
        // If no file but background_pdf is provided (URL or existing file)
        elseif ($request->has('background_pdf') && $request->background_pdf) {
            $backgroundPdf = $request->background_pdf;
            $template->background_pdf = $backgroundPdf;
            
            // If the background_pdf is a file URL, set as pdf_file too
            if (strpos($backgroundPdf, '/storage/') !== false) {
                $template->pdf_file = str_replace('/storage/', '', $backgroundPdf);
            }
        }

        // Update other fields
        $template->name = $request->name;
        $template->description = $request->description;
        $template->orientation = $request->orientation;
        $template->template_data = $templateData;
        
        // Update is_active field if it exists
        if (Schema::hasColumn('certificate_templates', 'is_active')) {
            $template->is_active = $request->has('is_active') ? $request->is_active : $template->is_active;
        }
        
        $template->save();

        return redirect()->route('template.designer')
            ->with('success', 'Template updated successfully.');
    }

    /**
     * Remove the specified template from storage.
     */
    public function destroy($id)
    {
        $template = CertificateTemplate::findOrFail($id);
        
        // Delete PDF file
        if ($template->pdf_file) {
            Storage::disk('public')->delete($template->pdf_file);
        }
        
        $template->delete();

        return redirect()->route('template.designer')
            ->with('success', 'Template deleted successfully.');
    }

    /**
     * Duplicate a template
     */
    public function duplicate($id)
    {
        $template = CertificateTemplate::findOrFail($id);
        
        $newTemplate = $template->replicate();
        $newTemplate->name = $template->name . ' (Copy)';
        $newTemplate->save();
        
        return redirect()->route('template.designer')
            ->with('success', 'Template duplicated successfully.');
    }

    /**
     * Upload a background image for the template
     */
    public function uploadBackground(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:10240', // 10MB max
        ]);
        
        // Store the file
        $path = $request->file('file')->store('certificate-templates', 'public');
        $url = Storage::url($path);
        
        return response()->json([
            'success' => true,
            'url' => $url,
            'path' => $path
        ]);
    }
}
