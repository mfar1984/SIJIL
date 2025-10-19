<x-guest-layout>
    <div class="max-w-5xl mx-auto py-8 px-4">
        <div class="bg-white rounded-lg shadow-md p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Privacy Policy</h1>
                <p class="text-xs text-gray-600">Last updated: {{ now()->format('d F Y') }}</p>
            </div>

            <!-- Content -->
            <div class="prose prose-sm max-w-none space-y-5 text-sm" style="line-height: 1.7;">
                
                <div class="text-sm mb-6">
                    <p class="mb-4 text-sm">E-Certificate ("we," "us," or "our") is committed to protecting and respecting your privacy. This Privacy Policy explains in detail how we collect, use, disclose, transfer, and safeguard your personal information when you use our electronic certificate management platform, mobile Progressive Web Application (PWA), and related services (collectively, the "Service").</p>
                    
                    <p class="mb-4 text-sm">We understand that you are trusting us with highly sensitive personal information, including identity documents, residential addresses, contact details, and professional information. This policy demonstrates our commitment to handling your data responsibly and in compliance with Malaysian Personal Data Protection Act (PDPA) 2010 and other applicable data protection regulations.</p>
                    
                    <p class="mb-4 text-sm">Please read this Privacy Policy carefully. By creating an account, registering for events, or using any part of our Service, you acknowledge that you have read, understood, and agree to be bound by this Privacy Policy. If you do not agree with our practices, please do not use our Service.</p>
                </div>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">1. Information We Collect</h2>
                
                <p class="mb-4 text-sm">We collect several categories of information to provide and improve our services. The extent of information collected depends on how you use our platform.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">1.1 Personal Identification Information</h3>
                <p class="text-sm mb-3">We collect sensitive personal information that is essential for certificate issuance, event management, and identity verification:</p>
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li><strong>Identity Documents:</strong> Malaysian Identity Card (IC) number or International Passport number. This information is used for unique participant identification, preventing duplicate registrations, and ensuring certificate authenticity. We recognize that IC and passport numbers are highly sensitive personal data and implement special security measures for their protection.</li>
                    <li><strong>Full Legal Name:</strong> Your name as it appears on official documents. This is used for accurate certificate generation and must match your identification documents for verification purposes.</li>
                    <li><strong>Contact Information:</strong> Email address (primary communication channel for event notifications, password resets, and certificate delivery) and mobile phone number (for SMS reminders and urgent event communications).</li>
                    <li><strong>Date of Birth:</strong> Collected for age verification (ensuring participants meet age requirements for certain events), demographic analytics, and statistical reporting to event organizers.</li>
                    <li><strong>Gender:</strong> Used for statistical purposes, demographic analysis, and when required by event organizers for compliance or reporting requirements.</li>
                    <li><strong>Race/Ethnicity:</strong> Collected only when specifically required by government-sponsored events or programs for compliance with national diversity and inclusion policies.</li>
                </ul>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">1.2 Residential Address Information</h3>
                <p class="mb-3 text-sm">We collect complete and detailed address information for several purposes including correspondence, certificate mailing (when physical certificates are requested), and verification of participant eligibility for location-specific events:</p>
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li><strong>Street Address:</strong> Address Line 1 and Address Line 2 for complete residential location</li>
                    <li><strong>City/Town:</strong> Your city or town of residence</li>
                    <li><strong>State/Province:</strong> Malaysian state or international province/region</li>
                    <li><strong>Postal/ZIP Code:</strong> For accurate mail delivery and location verification</li>
                    <li><strong>Country:</strong> Country of residence, particularly important for international participants</li>
                </ul>
                <p class="mb-4 text-sm">We understand that residential address is private information. This data is encrypted in our database and accessible only to authorized event organizers who have legitimate needs for communication or certificate delivery purposes.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">1.3 Professional & Employment Information</h3>
                <p class="mb-3 text-sm">Professional details are collected to customize certificates and enable professional networking features:</p>
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li><strong>Organization/Company Name:</strong> Your current employer, educational institution, or affiliated organization. This information may appear on certificates when requested by event organizers.</li>
                    <li><strong>Job Title/Position:</strong> Your current professional role or academic status (e.g., "Senior Manager," "Student," "Professor"). This helps event organizers understand participant demographics and may be included on professional development certificates.</li>
                    <li><strong>Department/Division:</strong> Your specific department or division within your organization, when relevant for specialized professional training events.</li>
                    <li><strong>Industry/Sector:</strong> The industry or sector you work in, used for event targeting and content customization.</li>
                </ul>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">1.4 Event Registration & Participation Data</h3>
                <p class="mb-3 text-sm">We maintain comprehensive records of your interaction with events:</p>
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li><strong>Registration Records:</strong> Date and time of event registration, registration method (web form, mobile app, bulk import), and registration status (confirmed, pending, cancelled)</li>
                    <li><strong>Attendance Records:</strong> Precise check-in and check-out timestamps for each event session, attendance status (present, late, absent, excused), and cumulative attendance duration</li>
                    <li><strong>Session Participation:</strong> Which specific sessions or segments of multi-day events you attended, including session timestamps and duration calculations</li>
                    <li><strong>QR Code Scanning Data:</strong> Timestamps and unique codes scanned for attendance verification, including the scanning device type (mobile app, web browser)</li>
                    <li><strong>Attendance Patterns:</strong> Historical attendance data across multiple events for analytics and reporting purposes</li>
                </ul>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">1.5 GPS Location Data</h3>
                <p class="mb-3 text-sm"><strong>IMPORTANT NOTICE:</strong> Geographic location data is considered sensitive personal information. We collect GPS coordinates only under specific circumstances with your explicit permission.</p>
                
                <p class="mb-3 text-sm"><strong>When GPS Data is Collected:</strong></p>
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li>When you use our mobile PWA to scan QR codes for attendance check-in</li>
                    <li>When you manually enter an attendance code through the mobile scanner</li>
                    <li>Only after you have granted location permission to your browser or mobile device</li>
                    <li>Separate permission request for each check-in/check-out action</li>
                </ul>
                
                <p class="mb-3 text-sm"><strong>What GPS Data We Collect:</strong></p>
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li>Precise latitude and longitude coordinates (accurate to approximately 10-50 meters depending on your device)</li>
                    <li>Timestamp of when location was captured</li>
                    <li>Accuracy radius of the GPS reading</li>
                    <li>Whether the location was captured during check-in or check-out</li>
                </ul>
                
                <p class="mb-3 text-sm"><strong>Why We Collect GPS Data:</strong></p>
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li>Verify that participants are physically present at the event venue (prevents remote or proxy attendance)</li>
                    <li>Enable event organizers to confirm legitimate attendance for certification purposes</li>
                    <li>Detect and prevent fraudulent attendance claims or certificate fraud</li>
                    <li>Generate location-verified attendance reports for compliance and audit purposes</li>
                    <li>Improve event planning by understanding participant travel patterns and venue accessibility</li>
                </ul>
                
                <p class="mb-4 text-sm"><strong>How GPS Data is Stored:</strong> GPS coordinates are stored in encrypted database fields alongside your attendance records. The data is linked to specific events and sessions, and is accessible only to the event organizer and system administrators. GPS data is never shared publicly, sold to third parties, or used for purposes other than attendance verification.</p>
                
                <p class="mb-4 text-sm"><strong>Your Control Over GPS:</strong> You can deny location permission at any time. If you deny permission, your attendance will still be recorded but without GPS verification. Event organizers may require GPS verification for certain events; in such cases, attendance without GPS may be flagged for manual verification.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">1.6 Technical & Device Information</h3>
                <p class="mb-3 text-sm">We automatically collect certain technical information when you use our Service:</p>
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li><strong>Device Information:</strong> Device type (smartphone, tablet, desktop), operating system (iOS, Android, Windows, macOS), browser type and version</li>
                    <li><strong>Login Activity:</strong> Login timestamps, logout times, session duration, and IP addresses used for access (for security monitoring and fraud detection)</li>
                    <li><strong>App Usage Data:</strong> Features used, pages visited, time spent on platform, and navigation patterns (for improving user experience)</li>
                    <li><strong>Performance Data:</strong> Load times, errors encountered, and system performance metrics (for technical optimization)</li>
                </ul>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">2. How We Use Your Information</h2>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">2.1 Certificate Generation & Management</h3>
                <p class="mb-4 text-sm">Your personal information, particularly your full name and IC/Passport number, is used to generate official digital certificates. Each certificate includes a unique certificate number that is cryptographically linked to your identity for verification purposes. Event organizers may include your organization and job title on certificates to demonstrate professional development credentials. We ensure that all information printed on certificates is accurate and matches your provided data.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">2.2 Event Registration & Management</h3>
                <p class="mb-3 text-sm">We use your information to:</p>
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li>Process your event registrations and maintain registration records</li>
                    <li>Send automated confirmation emails with event details, venue information, and attendance instructions</li>
                    <li>Deliver event reminders 24-48 hours before scheduled events via email and SMS</li>
                    <li>Communicate important updates, schedule changes, or event cancellations immediately</li>
                    <li>Generate participant lists and attendance rosters for event organizers</li>
                    <li>Manage waiting lists and capacity-limited events</li>
                    <li>Enable event organizers to contact you regarding specific events you registered for</li>
                </ul>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">2.3 Attendance Tracking & Verification</h3>
                <p class="mb-4 text-sm">Your attendance data, including GPS coordinates and timestamps, is used exclusively to verify and document your participation in events. We create detailed attendance records that include check-in/check-out times, session participation, and total attendance duration. This information is shared with event organizers to confirm attendance for continuing professional development (CPD) credits, academic credit hours, mandatory training compliance, or other certification requirements. GPS data adds an additional layer of verification to prevent attendance fraud and ensure the integrity of issued certificates.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">2.4 Communication & Notifications</h3>
                <p class="mb-3 text-sm">We use your email address and phone number for essential communications:</p>
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li><strong>Account Security:</strong> Password reset requests, suspicious login alerts, account activation emails, and security notifications</li>
                    <li><strong>Event Communications:</strong> Registration confirmations, event reminders (24h and 1h before), schedule changes, venue updates, and cancellation notices</li>
                    <li><strong>Certificate Delivery:</strong> Notifications when certificates are ready for download, including certificate number and download links</li>
                    <li><strong>Helpdesk Support:</strong> Responses to your support tickets and inquiries</li>
                    <li><strong>System Announcements:</strong> Critical service updates, maintenance schedules, and new feature announcements</li>
                </ul>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">2.5 Analytics & Service Improvement</h3>
                <p class="mb-4 text-sm">We analyze aggregated, anonymized data to understand usage patterns, improve our platform, identify technical issues, optimize user experience, and develop new features. Individual-level data is used only for providing services directly to you and is never shared in identifiable form for analytics purposes.</p>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">3. Information Sharing & Disclosure</h2>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">3.1 Sharing with Event Organizers</h3>
                <p class="mb-4 text-sm">When you register for an event, we share your registration information with that specific event's organizer. This includes your name, email address, phone number, organization, job title, and registration timestamp. Event organizers use this information solely for event management, participant communication, and attendance tracking. Each organizer can only access data for their own events and cannot view information from events organized by others. We contractually require event organizers to protect your data and use it only for legitimate event management purposes.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">3.2 Sharing with Other Participants</h3>
                <p class="mb-4 text-sm">Depending on event settings configured by the organizer, your name and organization may be visible to other registered participants through participant lists or networking features. However, we never publicly display or share with other participants your IC/Passport number, full address, phone number, date of birth, or any other sensitive personal data. You can request event organizers to exclude you from participant lists if you prefer to remain anonymous.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">3.3 Legal and Regulatory Disclosures</h3>
                <p class="mb-4 text-sm">We may disclose your personal information when required by law, court order, subpoena, or other legal process, or when we believe in good faith that disclosure is necessary to comply with legal obligations, protect our rights or property, investigate fraud or security issues, or protect the safety of our users or the public. We will attempt to notify you of such disclosures unless prohibited by law.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">3.4 Service Providers & Processors</h3>
                <p class="mb-3 text-sm">We engage carefully vetted third-party service providers to support our operations:</p>
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li><strong>Email Service Providers:</strong> SMTP servers, Mailgun, Amazon SES for sending transactional emails and notifications. These providers process email addresses and message content but do not store or use your data for their own purposes.</li>
                    <li><strong>SMS Gateway Providers:</strong> Infobip for SMS notifications. They process phone numbers and message content strictly according to our instructions.</li>
                    <li><strong>Cloud Infrastructure:</strong> Hosting providers for secure storage of databases, certificate PDF files, and application files. All data is encrypted at rest and in transit.</li>
                    <li><strong>Payment Processors:</strong> If paid events are offered, payment information is processed by PCI-DSS compliant payment gateways. We do not store credit card information on our servers.</li>
                </ul>
                <p class="mb-4 text-sm">All service providers are bound by strict data processing agreements and are prohibited from using your data for purposes other than providing services to us.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">3.5 No Sale of Personal Data</h3>
                <p class="mb-4 text-sm"><strong>We categorically do not sell, rent, lease, or trade your personal information to any third parties for any purpose, including marketing.</strong> Your IC number, passport number, address, phone number, and all other personal data are used exclusively to provide certificate and event management services. We do not monetize your personal data in any way.</p>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">4. Data Security & Protection Measures</h2>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">4.1 Technical Security Measures</h3>
                <p class="mb-3 text-sm">We implement comprehensive technical security controls to protect your personal information:</p>
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li><strong>SSL/TLS Encryption:</strong> All data transmission between your device and our servers uses industry-standard 256-bit SSL/TLS encryption. This ensures that IC numbers, passwords, and other sensitive data cannot be intercepted during transmission.</li>
                    <li><strong>Database Encryption:</strong> Sensitive fields (IC/Passport numbers, addresses, phone numbers) are encrypted at rest using AES-256 encryption standards.</li>
                    <li><strong>Password Hashing:</strong> User passwords are never stored in plain text. We use bcrypt hashing algorithm with salt, making it computationally infeasible to reverse-engineer passwords even in the unlikely event of database compromise.</li>
                    <li><strong>Secure Authentication:</strong> We use Laravel Sanctum for API authentication with token-based access control. Tokens expire after defined periods and can be revoked instantly if security is compromised.</li>
                    <li><strong>Regular Security Updates:</strong> Our platform and all dependencies are regularly updated to patch security vulnerabilities.</li>
                    <li><strong>Firewall Protection:</strong> Multi-layered firewall configurations protect our servers from unauthorized access attempts.</li>
                </ul>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">4.2 Organizational Security Measures</h3>
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li><strong>Role-Based Access Control:</strong> Access to sensitive data is strictly controlled through role and permission matrices. Administrators, organizers, and participants have clearly defined access levels.</li>
                    <li><strong>Activity Logging:</strong> All access to sensitive personal data (viewing, editing, exporting IC numbers, addresses, etc.) is logged with timestamps and user identification for audit trails.</li>
                    <li><strong>Staff Training:</strong> Personnel with access to the system are trained on data protection principles and confidentiality obligations.</li>
                    <li><strong>Data Minimization:</strong> We collect only the minimum information necessary for certificate issuance and event management.</li>
                    <li><strong>Regular Backups:</strong> Encrypted backups are performed daily and stored securely with access restricted to authorized technical staff only.</li>
                </ul>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">4.3 Special Protection for IC/Passport Numbers</h3>
                <p class="mb-4 text-sm">Recognizing the extreme sensitivity of identity document numbers, we implement additional protective measures:</p>
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li>IC and Passport numbers are stored in encrypted database columns with restricted query access</li>
                    <li>When displayed in administrative interfaces, numbers are masked (showing only last 4 digits: XXX-XX-1234)</li>
                    <li>Full IC/Passport numbers are visible only when absolutely necessary (certificate generation, identity verification)</li>
                    <li>Exporting participant data with IC numbers requires explicit permission and is logged</li>
                    <li>IC/Passport numbers are never included in email communications or displayed in browser URLs</li>
                    <li>Access to unmasked IC numbers is restricted to system administrators and specific event organizers who created the participant record</li>
                </ul>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">5. Data Retention Periods</h2>
                
                <p class="mb-4 text-sm">We retain different categories of information for varying periods based on legal requirements, business needs, and the nature of the data:</p>
                
                <ul class="list-disc list-outside ml-6 space-y-3 mb-4">
                    <li><strong>Account Information (IC, Passport, Address, Phone):</strong> Retained for the lifetime of your active account. After account deletion, personal identifiers are anonymized within 90 days, though anonymized analytics may be retained.</li>
                    <li><strong>Event Registration Records:</strong> Retained for 7 years from the event date. This extended retention period supports participants who need to verify past attendance for professional licensing, continuing education requirements, or employment verification.</li>
                    <li><strong>Digital Certificates:</strong> Retained permanently to enable long-term verification. Certificates may need to be verified years after issuance for career advancement, license renewals, or professional credentialing purposes.</li>
                    <li><strong>Attendance Records with GPS:</strong> Retained for 5 years from the event date, or longer if required by specific industry regulations (e.g., medical CPD, legal CLE). GPS coordinates are retained for the same period as attendance records.</li>
                    <li><strong>Communication Logs (Emails, SMS):</strong> Metadata (timestamps, recipients) retained for 2 years for customer service and dispute resolution purposes. Actual message content may be retained for shorter periods.</li>
                    <li><strong>Audit Logs:</strong> Security and access logs retained for 3 years to support security investigations and compliance audits.</li>
                </ul>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">6. Your Rights Under Malaysian PDPA 2010</h2>
                
                <p class="mb-4 text-sm">Under the Personal Data Protection Act 2010, you have comprehensive rights regarding your personal data. We are committed to facilitating the exercise of these rights promptly and transparently.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">6.1 Right of Access</h3>
                <p class="mb-4 text-sm">You have the right to request and receive a complete copy of all personal data we hold about you. This includes IC/Passport numbers, full addresses, phone numbers, GPS location history, all attendance records, certificates issued, and any notes or comments associated with your account. We will provide this information in a structured, commonly used, and machine-readable format (typically PDF or CSV) within 21 days of your request.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">6.2 Right to Correction</h3>
                <p class="mb-4 text-sm">You can update or correct your personal information at any time through your account profile. For critical data changes (name, IC number) that affect issued certificates, please contact the event organizer or system administrator. We will update records and may reissue certificates if necessary. Changes to historical attendance records require verification and approval from event organizers.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">6.3 Right to Deletion ("Right to be Forgotten")</h3>
                <p class="mb-4 text-sm">You can request complete deletion of your account and personal data. Upon deletion request, we will permanently delete your IC/Passport number, address, phone number, and login credentials within 30 days. However, please note that certificates and attendance records may be retained for legal compliance, audit purposes, and to prevent fraud. In such cases, records will be anonymized (name replaced with "Former Participant [ID]") while maintaining certificate validity.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">6.4 Right to Withdraw Consent</h3>
                <p class="mb-4 text-sm">You can withdraw consent for specific data processing activities. For GPS location tracking, revoke browser/app location permissions to stop future GPS data collection. For marketing communications, use the unsubscribe link in emails or update your communication preferences. Note that transactional emails (password resets, certificate deliveries) cannot be opted out as they are essential for service operation.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">6.5 Right to Data Portability</h3>
                <p class="mb-4 text-sm">You can request your data in a portable format to transfer to another service. We will provide your personal information, event registrations, attendance records, and certificates in commonly used electronic formats (PDF, CSV, JSON) within 21 days.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">6.6 Right to Object</h3>
                <p class="mb-4 text-sm">You can object to processing of your data for direct marketing, automated decision-making, or other purposes not essential to providing services you requested. We will cease such processing unless we have compelling legitimate grounds that override your interests.</p>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">7. International Data Transfers</h2>
                
                <p class="mb-4 text-sm">Your data is primarily stored on servers located in Malaysia. However, some of our service providers (email services, cloud infrastructure) may process data on servers located internationally, including in the United States, European Union, or Singapore. When data is transferred internationally, we ensure that adequate safeguards are in place through:</p>
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li>Standard Contractual Clauses approved by relevant data protection authorities</li>
                    <li>Service providers certified under recognized data protection frameworks</li>
                    <li>Encryption of data in transit and at rest</li>
                    <li>Regular audits of international processors' security practices</li>
                </ul>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">8. Children's Privacy</h2>
                
                <p class="mb-4 text-sm">Our Service is not directed to individuals under 13 years of age, and we do not knowingly collect personal information from children under 13. Parents or guardians registering minors aged 13-18 for events should provide their own contact information and supervise the minor's use of the Service. If we become aware that we have inadvertently collected data from a child under 13 without parental consent, we will take immediate steps to delete such information from our systems.</p>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">9. Data Breach Response</h2>
                
                <p class="mb-4 text-sm">Despite our robust security measures, no system is completely immune to security incidents. In the unlikely event of a data breach that affects your personal information, we have established comprehensive incident response procedures:</p>
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li><strong>Immediate Containment:</strong> We will immediately contain the breach, secure affected systems, and prevent further unauthorized access</li>
                    <li><strong>Investigation:</strong> Conduct thorough investigation to determine the scope, affected data, and root cause</li>
                    <li><strong>User Notification:</strong> Notify affected users within 72 hours via email, including details of what data was affected, potential consequences, and remedial actions taken</li>
                    <li><strong>Regulatory Notification:</strong> Report to Malaysian Personal Data Protection Commissioner as required by PDPA</li>
                    <li><strong>Remediation:</strong> Implement additional security measures to prevent recurrence and offer affected users assistance such as account monitoring or password resets</li>
                </ul>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">10. Cookies & Similar Technologies</h2>
                
                <p class="mb-4 text-sm">We use cookies and similar technologies to provide functionality, remember your preferences, and maintain security:</p>
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li><strong>Essential Cookies:</strong> Session cookies for maintaining your login state, CSRF tokens for security, and authentication tokens. These cookies are essential for Service operation and cannot be disabled.</li>
                    <li><strong>Preference Cookies:</strong> Remember your language preference, display settings, and interface customizations</li>
                    <li><strong>Local Storage:</strong> Used by our Progressive Web App to cache data for offline access, store authentication tokens, and enable app-like functionality</li>
                    <li><strong>Service Workers:</strong> Enable offline functionality and background synchronization for the mobile PWA</li>
                </ul>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">11. Changes to This Privacy Policy</h2>
                
                <p class="mb-4 text-sm">We reserve the right to modify this Privacy Policy at any time to reflect changes in our practices, technology, legal requirements, or business operations. When we make material changes that significantly affect how we handle your personal data, we will:</p>
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li>Update the "Last Updated" date at the top of this policy</li>
                    <li>Notify you via email to your registered email address</li>
                    <li>Display a prominent notice on our platform for 30 days</li>
                    <li>For significant changes, request your renewed consent before continuing to process your data under new terms</li>
                </ul>
                <p class="mb-4 text-sm">We encourage you to review this Privacy Policy periodically. Your continued use of the Service after changes are posted constitutes your acceptance of the revised Privacy Policy.</p>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">12. Contact Information & Data Protection Officer</h2>
                
                <p class="mb-3 text-sm">For any privacy-related questions, concerns, data access requests, or to exercise any of your rights under this Privacy Policy or PDPA 2010, please contact:</p>
                
                <div class="bg-gray-50 p-4 rounded border border-gray-200 my-4">
                    <p class="mb-2"><strong>Data Protection Officer</strong></p>
                    <p class="mb-2">E-Certificate Platform</p>
                    <p class="mb-2">Email: <strong class="text-primary-DEFAULT">privacy@e-certificate.com.my</strong></p>
                    <p class="mb-2">Response Time: Within 14 days as required by PDPA 2010</p>
                </div>
                
                <p class="mb-4 text-sm">Alternatively, you may contact your event organizer directly for event-specific data inquiries. Event organizers are responsible for managing participant data for their events and can assist with updates, corrections, or deletions.</p>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">13. Consent & Acknowledgment</h2>
                
                <p class="mb-4 text-sm">By creating an account, registering for events, or using any part of our Service, you acknowledge that you have read and understood this Privacy Policy in its entirety, and you consent to the collection, use, storage, and disclosure of your personal information as described herein.</p>
                
                <p class="mb-4 text-sm">For GPS location data specifically, you provide separate, explicit consent each time you grant location permission to your browser or mobile application. This consent can be withdrawn at any time by changing your device location settings.</p>
                
                <p class="mb-4 text-sm">If you do not agree with any part of this Privacy Policy, please do not use our Service or provide any personal information. Your use of the Service is voluntary, and you are free to decline providing optional information, though this may limit access to certain features or events.</p>
                
                <div class="bg-blue-50 border border-blue-200 p-4 rounded mt-6">
                    <p class="text-primary-DEFAULT font-semibold mb-2">Your Privacy Matters to Us</p>
                    <p>We are committed to transparency, security, and respect for your personal information. If you have any concerns about how your data is handled, please contact our Data Protection Officer immediately.</p>
                </div>
            </div>

            <!-- Back Button -->
            <div class="mt-8 text-center">
                <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 px-6 py-2 bg-primary-DEFAULT text-white rounded text-sm font-medium hover:bg-primary-dark transition">
                    <span class="material-icons" style="font-size: 18px;">arrow_back</span>
                    <span>Back</span>
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
