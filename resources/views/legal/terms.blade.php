<x-guest-layout>
    <div class="max-w-5xl mx-auto py-8 px-4">
        <div class="bg-white rounded-lg shadow-md p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Terms & Conditions</h1>
                <p class="text-xs text-gray-600">Last updated: {{ now()->format('d F Y') }}</p>
            </div>

            <!-- Content -->
            <div class="prose prose-sm max-w-none space-y-5" style="line-height: 1.7;">
                
                <div class="text-sm mb-6">
                    <p class="mb-4 text-sm">These Terms and Conditions ("Terms") govern your access to and use of the E-Certificate platform, including our website, mobile Progressive Web Application (PWA), APIs, and all related services (collectively, the "Service"). The Service is operated by E-Certificate ("we," "us," or "our").</p>
                    
                    <p class="mb-4 text-sm">Please read these Terms carefully before using the Service. By accessing or using the Service, creating an account, registering for events, or downloading certificates, you agree to be bound by these Terms. If you disagree with any part of these Terms, you must not use the Service.</p>
                    
                    <p class="mb-4 text-sm">These Terms constitute a legally binding agreement between you and E-Certificate. Your use of the Service is also governed by our Privacy Policy and Disclaimer, which are incorporated into these Terms by reference.</p>
                </div>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">1. Acceptance of Terms</h2>
                
                <p class="mb-4 text-sm">By creating an account on the E-Certificate platform, whether through web registration, mobile PWA, event registration forms, or administrator/organizer creation, you acknowledge that you have read, understood, and agree to be bound by these Terms, our Privacy Policy, and our Disclaimer.</p>
                
                <p class="mb-4 text-sm">If you are using the Service on behalf of an organization, company, educational institution, or government agency, you represent and warrant that you have the authority to bind that entity to these Terms, and "you" refers to both you individually and the entity you represent.</p>
                
                <p class="mb-4 text-sm">We reserve the right to modify, update, or replace these Terms at any time. Material changes will be communicated via email notification, platform announcements, or prominent notices. Continued use of the Service after changes constitutes acceptance of modified Terms. If you do not accept modified Terms, you must stop using the Service.</p>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">2. User Accounts & Registration</h2>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">2.1 Account Types</h3>
                <p class="mb-3 text-sm">The Service supports multiple account types with different privileges and responsibilities:</p>
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li><strong>Administrator Accounts:</strong> System-wide access to manage users, events, certificates, settings, and configurations. Administrators have elevated privileges and responsibilities for platform security and compliance.</li>
                    <li><strong>Organizer Accounts:</strong> Create and manage events, register participants, track attendance, issue certificates, and access reports for their own events only. Organizers are responsible for event content, participant data, and certificate validity.</li>
                    <li><strong>PWA Participant Accounts:</strong> Mobile-first accounts for participants to view registered events, check-in via QR scanning, download certificates, and manage personal profile. PWA accounts use email and password authentication with optional GPS location features.</li>
                    <li><strong>Regular Participant Records:</strong> Event-specific participant records created by organizers for attendance tracking and certificate issuance. These records may be linked to PWA accounts for unified participant management.</li>
                </ul>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">2.2 Account Creation Requirements</h3>
                <p class="mb-3 text-sm">When creating an account, you must:</p>
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li>Provide accurate, complete, and current information including your full legal name, valid email address, phone number, and identity card or passport number</li>
                    <li>Maintain and promptly update your account information to keep it accurate and current</li>
                    <li>Use your real name and genuine identity—impersonation or fake identities are strictly prohibited</li>
                    <li>Ensure your email address is valid and regularly monitored for important Service communications</li>
                    <li>Provide truthful professional information (organization, job title) that you are authorized to represent</li>
                </ul>
                
                <p class="mb-4 text-sm">Providing false, misleading, or fraudulent information constitutes a material breach of these Terms and may result in immediate account termination, certificate revocation, and potential legal action. We reserve the right to verify identity information and reject registrations we believe to be fraudulent.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">2.3 Account Security & Password Management</h3>
                <p class="mb-3 text-sm">You are solely responsible for:</p>
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li>Safeguarding your account password and keeping it confidential</li>
                    <li>Creating strong passwords (minimum 6 characters, recommended 12+ characters with mixed case, numbers, and symbols)</li>
                    <li>Not sharing your password with others or allowing others to access your account</li>
                    <li>Logging out from shared or public devices after using the Service</li>
                    <li>Notifying us immediately of any unauthorized account access or security breaches</li>
                    <li>All activities that occur under your account, whether authorized by you or not</li>
                </ul>
                
                <p class="mb-4 text-sm">We implement password hashing (bcrypt) and account lockout mechanisms (after multiple failed login attempts) for security. However, we are not liable for unauthorized account access resulting from password sharing, weak passwords, phishing attacks, or compromised credentials due to user negligence.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">2.4 Account Restrictions</h3>
                <p class="mb-3 text-sm">You may not:</p>
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li>Create multiple accounts for the same person to circumvent restrictions or obtain duplicate benefits</li>
                    <li>Share accounts with multiple individuals or create shared/generic accounts (except organizational accounts with proper authorization)</li>
                    <li>Sell, transfer, rent, or otherwise provide access to your account to third parties</li>
                    <li>Use automated systems, bots, or scripts to create accounts, register for events, or interact with the Service</li>
                    <li>Attempt to gain unauthorized access to other users' accounts or restricted system areas</li>
                </ul>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">3. Event Registration & Participation</h2>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">3.1 Event Registration Process</h3>
                <p class="mb-4 text-sm">Event registration through our platform creates a binding commitment between you and the event organizer (not E-Certificate). When you register for an event via web form, mobile PWA, or QR code link, you:</p>
                
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li>Agree to attend the event at the specified date, time, and venue</li>
                    <li>Acknowledge that you have read and understood the event description, requirements, schedule, and venue information</li>
                    <li>Consent to receive event-related communications (confirmations, reminders, updates) via email and SMS</li>
                    <li>Accept the event organizer's specific event terms, cancellation policies, and code of conduct (if applicable)</li>
                    <li>Authorize the event organizer to contact you using the information you provided</li>
                    <li>Understand that registration may be subject to organizer approval and is not always guaranteed</li>
                </ul>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">3.2 Event Capacity & Waitlists</h3>
                <p class="mb-4 text-sm">Events may have participant capacity limits. Registration does not guarantee entry if capacity is reached. Event organizers may implement first-come-first-served policies, waitlists, approval processes, or priority systems. We are not responsible for denial of entry due to capacity constraints or organizer selection criteria.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">3.3 Event Changes & Cancellations</h3>
                <p class="mb-4 text-sm">Event organizers reserve the right to modify event details (dates, times, venues, speakers, content, format) or cancel events due to circumstances including but not limited to low registration numbers, speaker unavailability, venue issues, weather conditions, or force majeure events. While we provide notification tools, event organizers are responsible for communicating changes to participants.</p>
                
                <p class="mb-4 text-sm">We are not liable for expenses, inconveniences, or losses you incur due to event changes or cancellations, including non-refundable travel bookings, accommodation costs, time off work, or opportunity costs. Refund policies are determined solely by event organizers, not by E-Certificate.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">3.4 Participant Conduct</h3>
                <p class="mb-3 text-sm">When participating in events, you agree to:</p>
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li>Behave professionally, respectfully, and courteously toward other participants, speakers, organizers, and venue staff</li>
                    <li>Comply with event organizer's rules, codes of conduct, dress codes, and venue policies</li>
                    <li>Not disrupt events through harassment, aggressive behavior, excessive noise, or other inappropriate conduct</li>
                    <li>Respect intellectual property rights of speakers and presenters (no unauthorized recording, photographing, or redistribution of content)</li>
                    <li>Attend events you registered for or cancel with reasonable notice if unable to attend</li>
                </ul>
                
                <p class="mb-4 text-sm">Violation of conduct standards may result in removal from events, account suspension, and prohibition from future events. Event organizers have sole discretion to enforce conduct policies.</p>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">4. Attendance Verification & GPS Tracking</h2>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">4.1 QR Code Attendance System</h3>
                <p class="mb-4 text-sm">Our platform provides QR code-based attendance tracking. Event organizers generate unique QR codes for attendance sessions. Participants scan these codes using the mobile PWA scanner or manually enter attendance codes. By using the attendance system, you agree that:</p>
                
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li>QR codes are for your personal use only and must not be shared, photographed for others, or used to record proxy attendance</li>
                    <li>Each participant must personally scan their own attendance code at the physical event location</li>
                    <li>Scanning attendance codes on behalf of absent participants constitutes fraud and is grounds for certificate revocation and account termination</li>
                    <li>Attendance codes are time-sensitive and venue-specific—codes may only be scanned during designated time windows</li>
                </ul>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">4.2 GPS Location Verification</h3>
                <p class="mb-4 text-sm">When you use the mobile PWA attendance scanner, the system requests permission to access your device's GPS location. By granting location permission, you explicitly consent to:</p>
                
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li>Collection of your precise GPS coordinates (latitude and longitude) at the moment of check-in and check-out</li>
                    <li>Storage of GPS coordinates in our database linked to your attendance record</li>
                    <li>Event organizers accessing your GPS data to verify physical presence at event venue</li>
                    <li>Use of GPS data as evidence of attendance authenticity for certificate validation</li>
                </ul>
                
                <p class="mb-4 text-sm">You may deny location permission, but event organizers may require GPS verification for attendance validity. Events with mandatory GPS verification may flag non-GPS attendance for manual review or may not issue certificates for attendance recorded without GPS confirmation.</p>
                
                <p class="mb-4 text-sm">You agree not to use GPS spoofing, fake location apps, VPNs with location masking, or other technological means to falsify your location during attendance check-in. Such actions constitute fraud and will result in certificate revocation, account termination, and potential legal action.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">4.3 Attendance Accuracy</h3>
                <p class="mb-4 text-sm">You acknowledge that attendance records (check-in/check-out times, session participation, total duration) are used to determine certificate eligibility. Many events require minimum attendance thresholds (e.g., 75% attendance) for certificate issuance. You are responsible for ensuring you meet attendance requirements.</p>
                
                <p class="mb-4 text-sm">Late check-ins, early check-outs, or missed sessions may affect certificate eligibility or may be noted on certificates (e.g., "partial attendance"). Attendance status determinations (on-time, late, absent) are final and based on system-recorded timestamps compared to session start times.</p>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">5. Digital Certificates</h2>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">5.1 Certificate Generation</h3>
                <p class="mb-4 text-sm">Digital certificates are automatically or manually generated by event organizers based on attendance records, event participation, and organizer-defined criteria. Certificate generation is at the sole discretion of event organizers. We provide the technology platform but do not make decisions about who receives certificates or what certificates contain.</p>
                
                <p class="mb-4 text-sm">Certificates include your name (as provided in your profile), event name, event date(s), certificate number (unique identifier), issuance date, and may include additional information such as organization, job title, IC number (last 4 digits), attendance duration, or achievement details as determined by the event organizer.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">5.2 Certificate Ownership & Intellectual Property</h3>
                <p class="mb-4 text-sm">Certificates are the intellectual property of the event organizer who issued them. Event organizers retain all rights to certificate designs, templates, logos, branding, and content. You receive a limited, non-exclusive, non-transferable license to download, store, print, and present your personal certificates for legitimate purposes (employment verification, license renewal, academic records).</p>
                
                <p class="mb-3 text-sm">You may not:</p>
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li>Modify, alter, edit, or manipulate certificates in any way</li>
                    <li>Remove watermarks, certificate numbers, security features, or organizer information</li>
                    <li>Create derivative works or templates based on certificates</li>
                    <li>Claim certificates issued to others as your own</li>
                    <li>Sell, transfer, or provide your certificates to others for fraudulent use</li>
                </ul>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">5.3 Certificate Validity & Verification</h3>
                <p class="mb-4 text-sm">Each certificate includes a unique certificate number in the format CERT-YYYYMMDDHHMMSS-XXXXX for authentication purposes. This number can be used by employers, licensing boards, or educational institutions to verify certificate authenticity with the issuing event organizer.</p>
                
                <p class="mb-4 text-sm">Certificate validity, recognition, and acceptance for professional licensing, continuing education credits, academic requirements, or employment purposes are determined solely by the receiving institution or regulatory authority. We make no representations regarding certificate acceptance and you should verify acceptance requirements independently before relying on certificates for professional or academic purposes.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">5.4 Certificate Revocation</h3>
                <p class="mb-4 text-sm">Certificates may be revoked by event organizers or system administrators if:</p>
                
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li>Attendance fraud is discovered (proxy attendance, GPS spoofing, QR code sharing)</li>
                    <li>Participant provided false information during registration</li>
                    <li>Participant violated event code of conduct or was removed from the event</li>
                    <li>Minimum attendance requirements were not actually met due to data errors</li>
                    <li>Certificate was issued in error or contains incorrect information</li>
                    <li>Participant requests certificate withdrawal</li>
                </ul>
                
                <p class="mb-4 text-sm">Upon revocation, certificate files may be deleted from your account and verification systems will reflect revocation status. You must discontinue use of revoked certificates immediately and notify entities to whom you previously presented the certificate.</p>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">6. Mobile Progressive Web App (PWA)</h2>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">6.1 PWA Functionality</h3>
                <p class="mb-4 text-sm">Our mobile PWA provides app-like functionality including offline access, push notifications, home screen installation, and device integration features. The PWA requires modern browsers (Chrome, Safari, Edge, Firefox) with JavaScript enabled and support for service workers, local storage, and web APIs.</p>
                
                <p class="mb-4 text-sm">PWA features depend on device capabilities and permissions. By using the PWA, you agree to grant necessary permissions including camera access (for QR scanning), GPS location (for attendance verification), notification permissions (for event alerts), and local storage (for offline data caching). Denying permissions may limit functionality.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">6.2 Camera Access for QR Scanning</h3>
                <p class="mb-4 text-sm">The PWA scanner feature uses your device camera to scan QR codes for attendance check-in. Camera access is requested only when you navigate to the scanner page. We do not record video, take photographs (beyond QR code recognition), or access your photo gallery. Camera access is used exclusively for real-time QR code scanning and no images are stored.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">6.3 Push Notifications</h3>
                <p class="mb-4 text-sm">If you enable push notifications, we may send notifications about upcoming events, event changes, certificate availability, password resets, and important system announcements using Firebase Cloud Messaging (FCM). You can disable notifications through device settings or PWA settings at any time. We do not send marketing notifications without explicit consent.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">6.4 Offline Functionality</h3>
                <p class="mb-4 text-sm">The PWA uses service workers to cache certain data for offline access. Cached data may include previously viewed events, downloaded certificates, and profile information. Offline functionality is limited—certain features (new registrations, attendance scanning, certificate downloads) require internet connectivity. We are not liable for data loss if cached data is cleared or if offline functionality fails.</p>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">7. Prohibited Uses & User Conduct</h2>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">7.1 Prohibited Activities</h3>
                <p class="mb-3 text-sm">You expressly agree not to:</p>
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li><strong>Fraud & Misrepresentation:</strong> Create fraudulent certificates, falsify attendance records, use fake identities, provide false IC/passport numbers, or misrepresent your credentials, qualifications, or organizational affiliations</li>
                    <li><strong>Impersonation:</strong> Impersonate another person, entity, event organizer, or system administrator, or falsely claim affiliation with organizations</li>
                    <li><strong>System Interference:</strong> Interfere with, disrupt, or impose unreasonable load on the Service through denial-of-service attacks, spam, flooding, excessive API requests, or other abusive behavior</li>
                    <li><strong>Unauthorized Access:</strong> Attempt to gain unauthorized access to accounts, databases, servers, networks, or restricted areas through hacking, password mining, social engineering, SQL injection, or other attack methods</li>
                    <li><strong>Data Scraping:</strong> Use automated systems, bots, crawlers, or scrapers to extract data from the Service without written permission</li>
                    <li><strong>Reverse Engineering:</strong> Decompile, reverse engineer, disassemble, or attempt to derive source code of the Service</li>
                    <li><strong>Malicious Code:</strong> Upload viruses, malware, trojans, worms, or other malicious code or engage in activities that could damage, disable, or impair the Service</li>
                    <li><strong>Certificate Forgery:</strong> Create fake certificates, modify genuine certificates, duplicate others' certificates, or use certificate generation tools for fraudulent purposes</li>
                </ul>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">7.2 Legal Compliance</h3>
                <p class="mb-4 text-sm">You agree to use the Service only for lawful purposes and in compliance with all applicable Malaysian laws and regulations, including but not limited to Personal Data Protection Act 2010, Communications and Multimedia Act 1998, Computer Crimes Act 1997, and Penal Code provisions regarding fraud and forgery.</p>
                
                <p class="mb-3 text-sm">Prohibited activities include:</p>
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li>Using the Service to facilitate illegal activities or criminal conduct</li>
                    <li>Violating export control laws or sanctions regulations</li>
                    <li>Infringing intellectual property rights of others</li>
                    <li>Transmitting defamatory, harassing, threatening, or discriminatory content</li>
                    <li>Collecting personal information of other users without consent</li>
                </ul>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">8. Intellectual Property Rights</h2>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">8.1 Platform Ownership</h3>
                <p class="mb-4 text-sm">The Service and its original content (excluding user-generated content and event organizer content), features, functionality, design, code, databases, graphics, logos, and other elements are owned by E-Certificate and are protected by international copyright, trademark, patent, trade secret, and other intellectual property laws.</p>
                
                <p class="mb-4 text-sm">You may not copy, modify, distribute, sell, lease, reverse engineer, or create derivative works of any part of the Service without our express written permission. The E-Certificate name, logo, and trademarks are our exclusive property and may not be used without authorization.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">8.2 User Content License</h3>
                <p class="mb-4 text-sm">When you submit content to the Service (profile information, survey responses, helpdesk messages, file attachments, event feedback), you grant us a worldwide, non-exclusive, royalty-free, transferable license to use, reproduce, store, process, and display such content solely for purposes of providing and improving the Service.</p>
                
                <p class="mb-4 text-sm">You retain ownership of your submitted content and can request deletion at any time (subject to retention requirements for certificates and attendance records). You represent and warrant that you own or have necessary rights to all content you submit and that your content does not violate third-party rights or applicable laws.</p>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">9. Payment & Fees</h2>
                
                <p class="mb-4 text-sm">The basic E-Certificate platform service is provided to participants free of charge. However, individual events may require payment of registration fees, which are set and collected by event organizers. Payment processing is handled through third-party payment gateways.</p>
                
                <p class="mb-4 text-sm">All fees, payment terms, and refund policies are determined solely by event organizers. We do not set prices, collect payments on our own behalf (beyond platform subscription fees if applicable), or issue refunds. Payment disputes must be resolved with event organizers.</p>
                
                <p class="mb-4 text-sm">You are responsible for all applicable taxes, transaction fees, currency conversion charges, and other payment-related costs. We are not liable for payment processing failures, delayed refunds, or payment gateway security issues.</p>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">10. Account Termination & Suspension</h2>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">10.1 Termination by You</h3>
                <p class="mb-4 text-sm">You may terminate your account at any time by contacting event organizers or system administrators. Upon termination, your login access will be disabled. However, historical event registrations, attendance records, and issued certificates may be retained for archival and verification purposes as described in our Privacy Policy.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">10.2 Termination by Us</h3>
                <p class="mb-4 text-sm">We reserve the right to suspend, deactivate, or terminate your account and access to the Service immediately, without prior notice or liability, for any reason including:</p>
                
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li>Violation of these Terms, Privacy Policy, or Disclaimer</li>
                    <li>Fraudulent activity (attendance fraud, certificate forgery, identity fraud)</li>
                    <li>Providing false or misleading information</li>
                    <li>Abusive, threatening, or harassing behavior toward staff, organizers, or other participants</li>
                    <li>System abuse, hacking attempts, or security violations</li>
                    <li>Non-payment of fees (if applicable)</li>
                    <li>Prolonged account inactivity (subject to notification)</li>
                    <li>Legal or regulatory requirements</li>
                </ul>
                
                <p class="mb-4 text-sm">Upon termination, your right to use the Service ceases immediately. We may delete or archive your data in accordance with our retention policies. Certificates issued before termination remain valid unless revoked for cause.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">10.3 Effects of Termination</h3>
                <p class="mb-4 text-sm">Termination does not affect obligations incurred before termination (outstanding payments, attendance commitments, data retention obligations). Provisions of these Terms that by their nature should survive termination (intellectual property, liability limitations, dispute resolution, governing law) remain in effect after termination.</p>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">11. Data Protection & Privacy</h2>
                
                <p class="mb-4 text-sm">Your use of the Service involves processing of personal data including highly sensitive information (IC numbers, passport numbers, addresses, phone numbers, GPS coordinates). Data protection is governed by our comprehensive Privacy Policy, which is incorporated into these Terms.</p>
                
                <p class="mb-4 text-sm">By using the Service, you consent to data collection, processing, and sharing as described in the Privacy Policy. You acknowledge that event organizers will have access to your personal information for events you register for. You understand that GPS location data will be collected and stored if you grant location permissions.</p>
                
                <p class="mb-4 text-sm">You have rights under Malaysian PDPA 2010 including rights to access, correct, delete, and port your data. Exercise of these rights is described in our Privacy Policy. However, certain data (issued certificates, historical attendance records) may be retained for legal compliance even if you delete your account.</p>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">12. Indemnification</h2>
                
                <p class="mb-4 text-sm">You agree to indemnify, defend, and hold harmless E-Certificate, its directors, officers, employees, agents, partners, and affiliates from and against any and all claims, liabilities, damages, losses, costs, expenses (including reasonable attorneys' fees) arising from or related to:</p>
                
                <ul class="list-disc list-outside ml-6 space-y-2 mb-4 text-sm">
                    <li>Your use or misuse of the Service</li>
                    <li>Your violation of these Terms or applicable laws</li>
                    <li>Your violation of third-party rights (intellectual property, privacy, publicity)</li>
                    <li>False or fraudulent information you provided</li>
                    <li>Attendance fraud or certificate misuse</li>
                    <li>Disputes with event organizers or other participants</li>
                    <li>Unauthorized account access due to your negligence</li>
                </ul>
                
                <p class="mb-4 text-sm">This indemnification obligation survives termination of your account and these Terms.</p>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">13. Dispute Resolution & Governing Law</h2>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">13.1 Governing Law</h3>
                <p class="mb-4 text-sm">These Terms shall be governed by and construed in accordance with the laws of Malaysia, without regard to its conflict of law provisions. The United Nations Convention on Contracts for the International Sale of Goods does not apply to these Terms.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">13.2 Jurisdiction</h3>
                <p class="mb-4 text-sm">You agree to submit to the exclusive jurisdiction of the courts located in Kuala Lumpur, Malaysia for resolution of any disputes arising from or related to these Terms or your use of the Service. You waive any objections to venue or jurisdiction in Malaysian courts.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">13.3 Dispute Resolution Process</h3>
                <p class="mb-4 text-sm">Before initiating formal legal proceedings, parties agree to attempt good-faith negotiation to resolve disputes. Disputes should first be reported to our support team with detailed explanation. We will investigate and attempt resolution within 30 days. If negotiation fails, parties may pursue formal legal remedies.</p>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">14. Severability & Waiver</h2>
                
                <p class="mb-4 text-sm">If any provision of these Terms is found to be unenforceable, invalid, or illegal by a court of competent jurisdiction, such provision shall be modified to the minimum extent necessary to make it enforceable, or if modification is not possible, shall be severed from these Terms. The remaining provisions shall continue in full force and effect.</p>
                
                <p class="mb-4 text-sm">Our failure to enforce any right or provision of these Terms shall not constitute a waiver of such right or provision. Waiver of any breach or default shall not waive any subsequent breach or default. All waivers must be in writing to be effective.</p>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">15. Entire Agreement</h2>
                
                <p class="mb-4 text-sm">These Terms, together with our Privacy Policy and Disclaimer, constitute the entire agreement between you and E-Certificate regarding the Service and supersede all prior agreements, understandings, representations, and communications (written or oral) regarding the subject matter herein.</p>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">16. Contact Information</h2>
                
                <p class="mb-4 text-sm">For questions, concerns, or notices regarding these Terms, please contact:</p>
                
                <div class="bg-gray-50 p-4 rounded border border-gray-200 my-4 text-sm">
                    <p class="mb-2 text-sm"><strong>E-Certificate Legal</strong></p>
                    <p class="mb-2 text-sm">Email: <strong class="text-primary-DEFAULT">legal@e-certificate.com.my</strong></p>
                    <p class="text-sm">For event-specific issues, contact the relevant event organizer directly.</p>
                </div>
                
                <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">17. Acknowledgment</h2>
                
                <p class="mb-4 text-sm">BY USING THE E-CERTIFICATE SERVICE, YOU ACKNOWLEDGE THAT YOU HAVE READ THESE TERMS OF SERVICE, UNDERSTAND THEM, AND AGREE TO BE BOUND BY THEM. IF YOU DO NOT AGREE TO THESE TERMS, YOU MUST NOT ACCESS OR USE THE SERVICE.</p>
                
                <div class="bg-blue-50 border border-blue-200 p-4 rounded mt-6 text-sm">
                    <p class="text-primary-DEFAULT font-semibold mb-2 text-sm">Important Reminder</p>
                    <p class="text-sm">These Terms are designed to protect both you and us. If you have any questions or concerns about these Terms, please contact us before using the Service. We are committed to transparency and fair treatment of all users.</p>
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
