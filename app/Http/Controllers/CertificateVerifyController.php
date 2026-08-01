<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;

/**
 * The public certificate check.
 *
 * A holder shares a link; whoever receives it can confirm the certificate is real
 * without an account. This is the same idea as a university checking a diploma
 * number, and it is what makes the app's share button worth pressing: before this
 * there was no page a recipient could open, so the app shared its own certificates
 * screen, which asks the recipient to sign in and then shows them their own
 * certificates instead.
 *
 * What it deliberately does NOT do:
 *
 *  - It never serves the PDF. The file carries a signature and layout that is worth
 *    more than the fact of the award, and the person holding the link is not
 *    necessarily the person the certificate belongs to.
 *  - It never shows contact details. Name, event and date are the whole point;
 *    email, phone, IC and address are not, and this page is open to the internet.
 *  - It does not confirm or deny in a way that helps someone walk the number space.
 *    The route is rate limited and certificate numbers carry a random suffix.
 */
class CertificateVerifyController extends Controller
{
    /**
     * Show the result for a certificate number.
     *
     * Always answers with a page rather than a 404, so a mistyped number reads as
     * "we cannot verify this" rather than as a broken link.
     */
    public function show(Request $request, string $number)
    {
        $certificate = Certificate::query()
            ->where('certificate_number', $number)
            ->with(['event:id,name,start_date,end_date,location,user_id', 'event.user:id,name', 'participant:id,name'])
            ->first();

        return view('public.certificate-verify', [
            'number' => $number,
            'certificate' => $certificate,
        ]);
    }

    /**
     * The form for someone who has a number but no link.
     */
    public function form()
    {
        return view('public.certificate-verify', [
            'number' => null,
            'certificate' => null,
        ]);
    }

    /**
     * Accept a typed number and redirect to its own URL, so the result is
     * shareable and bookmarkable in turn.
     */
    public function lookup(Request $request)
    {
        $validated = $request->validate([
            'certificate_number' => 'required|string|max:100',
        ]);

        return redirect()->route('certificate.verify', [
            'number' => trim($validated['certificate_number']),
        ]);
    }
}
