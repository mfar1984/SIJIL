<?php

namespace App\Http\Controllers;

use App\Models\CertificateTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TemplateDesignerController extends Controller
{
    /**
     * Display a listing of the templates.
     */
    public function index()
    {
        $templates = CertificateTemplate::orderBy('created_at', 'desc')->get();
        
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
     * Store a newly created template in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pdf_file' => 'required|file|mimes:pdf|max:10240', // 10MB max
            'orientation' => 'required|in:portrait,landscape',
        ]);

        // Store the PDF file
        $pdfPath = $request->file('pdf_file')->store('certificate-templates', 'public');

        // Create the template
        $template = CertificateTemplate::create([
            'name' => $request->name,
            'description' => $request->description,
            'pdf_file' => $pdfPath,
            'orientation' => $request->orientation,
            'placeholders' => [],
            'created_by' => auth()->id(),
        ]);

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
            'orientation' => 'required|in:portrait,landscape',
        ]);

        $template = CertificateTemplate::findOrFail($id);

        // Update PDF file if provided
        if ($request->hasFile('pdf_file')) {
            // Delete old file if it exists
            if ($template->pdf_file) {
                Storage::disk('public')->delete($template->pdf_file);
            }
            
            // Store new file
            $pdfPath = $request->file('pdf_file')->store('certificate-templates', 'public');
            $template->pdf_file = $pdfPath;
        }

        // Update other fields
        $template->name = $request->name;
        $template->description = $request->description;
        $template->orientation = $request->orientation;
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
     * Show the template editor.
     */
    public function editor($id)
    {
        $template = CertificateTemplate::findOrFail($id);
        
        // Convert placeholders to mm if needed
        $template->convertPlaceholdersToMm();
        
        return view('templates.editor', compact('template'));
    }

    /**
     * Save the template design.
     */
    public function saveEditor(Request $request, $id)
    {
        try {
            Log::info('Saving editor template', [
                'template_id' => $id,
                'request_data' => $request->all()
            ]);
            
            $request->validate([
                'placeholders' => 'required|array',
            ]);

            $template = CertificateTemplate::findOrFail($id);
            
            // Ensure all placeholders use the correct format
            $placeholders = $request->placeholders;
            
            Log::info('Processing placeholders', [
                'count' => count($placeholders),
                'placeholders' => $placeholders
            ]);
            
            foreach ($placeholders as $key => $placeholder) {
                // Ensure placeholder type uses {{name}} format
                if (isset($placeholder['type'])) {
                    $type = $placeholder['type'];
                    if (strpos($type, '{{') !== 0 || strpos($type, '}}') !== strlen($type) - 2) {
                        // Remove any existing braces
                        $cleanType = trim($type, '{}');
                        $placeholders[$key]['type'] = '{{' . $cleanType . '}}';
                    }
                }
                
                // Ensure coordinates and font size are stored as numbers
                if (isset($placeholder['x'])) {
                    $placeholders[$key]['x'] = (float) $placeholder['x'];
                }
                
                if (isset($placeholder['y'])) {
                    $placeholders[$key]['y'] = (float) $placeholder['y'];
                }
                
                if (isset($placeholder['fontSize'])) {
                    $placeholders[$key]['fontSize'] = (float) $placeholder['fontSize'];
                }
            }
            
            Log::info('Saving processed placeholders', [
                'processed_placeholders' => $placeholders
            ]);
            
            $template->placeholders = $placeholders;
            $template->save();

            return response()->json([
                'success' => true,
                'message' => 'Template design saved successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving template editor', [
                'template_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
