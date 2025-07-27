<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $survey->title }} - Survey</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#3b82f6',
                            dark: '#1d4ed8',
                        },
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
        .required-asterisk {
            color: #ef4444;
        }
        .survey-progress-bar {
            height: 6px;
            background-color: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }
        .survey-progress-value {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%);
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
        <!-- Header with Logo -->
        <div class="mb-8 text-center">
            <div class="flex justify-center">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-12">
            </div>
        </div>
        
        <!-- Survey Container -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <!-- Survey Header -->
            <div class="p-6 bg-primary-DEFAULT text-white">
                <h1 class="text-2xl font-bold mb-2">{{ $survey->title }}</h1>
                @if($survey->description)
                    <p class="text-sm text-blue-100">{!! nl2br(e($survey->description)) !!}</p>
                @endif
            </div>
            
            <!-- Progress Bar -->
            <div class="px-6 py-3 bg-blue-50 border-b border-blue-100">
                <div class="survey-progress-bar">
                    <div class="survey-progress-value" id="survey-progress" style="width: 0%"></div>
                </div>
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                    <span>Question <span id="current-question">1</span> of {{ $survey->questions->count() }}</span>
                    <span id="progress-percentage">0%</span>
                </div>
            </div>
            
            <!-- Survey Form -->
            <form id="survey-form" action="{{ route('public.survey.submit', $survey->slug) }}" method="POST" class="p-6">
                @csrf
                
                <!-- Questions Container -->
                <div id="questions-container">
                    @foreach($survey->questions as $index => $question)
                        <div class="question-slide mb-8 {{ $index > 0 ? 'hidden' : '' }}" data-question-index="{{ $index }}">
                            <div class="mb-4">
                                <div class="flex items-start">
                                    <h3 class="text-lg font-medium text-gray-900">
                                        {{ $question->question_text }}
                                        @if($question->required)
                                            <span class="required-asterisk">*</span>
                                        @endif
                                    </h3>
                                </div>
                                
                                @if($question->description)
                                    <p class="text-sm text-gray-500 mt-1">{{ $question->description }}</p>
                                @endif
                            </div>
                            
                            <div class="mt-4">
                                @switch($question->question_type)
                                    @case('text')
                                        <input 
                                            type="text" 
                                            name="question_{{ $question->id }}" 
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                            {{ $question->required ? 'required' : '' }}
                                        >
                                        @break
                                        
                                    @case('textarea')
                                        <textarea 
                                            name="question_{{ $question->id }}" 
                                            rows="3"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                            {{ $question->required ? 'required' : '' }}
                                        ></textarea>
                                        @break
                                        
                                    @case('multiple_choice')
                                        @if($question->options)
                                            <div class="space-y-3">
                                                @foreach($question->options as $option)
                                                    <div class="flex items-center">
                                                        <input 
                                                            type="radio" 
                                                            id="option_{{ $question->id }}_{{ $loop->index }}" 
                                                            name="question_{{ $question->id }}" 
                                                            value="{{ $option }}"
                                                            class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500"
                                                            {{ $question->required ? 'required' : '' }}
                                                        >
                                                        <label for="option_{{ $question->id }}_{{ $loop->index }}" class="ml-3 block text-sm text-gray-700">
                                                            {{ $option }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                        @break
                                        
                                    @case('checkbox')
                                        @if($question->options)
                                            <div class="space-y-3">
                                                @foreach($question->options as $option)
                                                    <div class="flex items-center">
                                                        <input 
                                                            type="checkbox" 
                                                            id="option_{{ $question->id }}_{{ $loop->index }}" 
                                                            name="question_{{ $question->id }}[]" 
                                                            value="{{ $option }}"
                                                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                        >
                                                        <label for="option_{{ $question->id }}_{{ $loop->index }}" class="ml-3 block text-sm text-gray-700">
                                                            {{ $option }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @if($question->required)
                                                <input type="hidden" name="_{{ $question->id }}_required" class="checkbox-required">
                                            @endif
                                        @endif
                                        @break
                                        
                                    @case('dropdown')
                                        @if($question->options)
                                            <select 
                                                name="question_{{ $question->id }}" 
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                                {{ $question->required ? 'required' : '' }}
                                            >
                                                <option value="">-- Select an option --</option>
                                                @foreach($question->options as $option)
                                                    <option value="{{ $option }}">{{ $option }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                        @break
                                        
                                    @case('rating')
                                        <div class="flex space-x-4">
                                            @for($i = 1; $i <= 5; $i++)
                                                <div class="flex flex-col items-center">
                                                    <input 
                                                        type="radio" 
                                                        id="rating_{{ $question->id }}_{{ $i }}" 
                                                        name="question_{{ $question->id }}" 
                                                        value="{{ $i }}"
                                                        class="h-8 w-8 border-gray-300 text-blue-600 focus:ring-blue-500"
                                                        {{ $question->required ? 'required' : '' }}
                                                    >
                                                    <label for="rating_{{ $question->id }}_{{ $i }}" class="mt-1 text-sm text-gray-700">
                                                        {{ $i }}
                                                    </label>
                                                </div>
                                            @endfor
                                        </div>
                                        @break
                                        
                                    @case('date')
                                        <input 
                                            type="date" 
                                            name="question_{{ $question->id }}" 
                                            class="block w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                            {{ $question->required ? 'required' : '' }}
                                        >
                                        @break
                                @endswitch
                            </div>
                        </div>
                    @endforeach
                    
                    <!-- Respondent Information (if anonymous responses not allowed) -->
                    @if(!$survey->allow_anonymous)
                        <div class="question-slide mb-8 hidden" data-question-index="{{ $survey->questions->count() }}">
                            <div class="mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Your Information</h3>
                                <p class="text-sm text-gray-500 mt-1">Please provide your contact information to complete the survey.</p>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="respondent_name" class="block text-sm font-medium text-gray-700">
                                        Name <span class="required-asterisk">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        id="respondent_name" 
                                        name="respondent_name" 
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                    >
                                </div>
                                
                                <div>
                                    <label for="respondent_email" class="block text-sm font-medium text-gray-700">
                                        Email <span class="required-asterisk">*</span>
                                    </label>
                                    <input 
                                        type="email" 
                                        id="respondent_email" 
                                        name="respondent_email" 
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                    >
                                </div>
                                
                                <div>
                                    <label for="respondent_phone" class="block text-sm font-medium text-gray-700">
                                        Phone Number
                                    </label>
                                    <input 
                                        type="text" 
                                        id="respondent_phone" 
                                        name="respondent_phone"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                    >
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Navigation Buttons -->
                <div class="flex justify-between mt-8">
                    <button 
                        type="button" 
                        id="prev-btn"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hidden"
                    >
                        Previous
                    </button>
                    
                    <div class="flex-grow"></div>
                    
                    <button 
                        type="button" 
                        id="next-btn"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
                    >
                        Next
                    </button>
                    
                    <button 
                        type="submit" 
                        id="submit-btn"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 hidden"
                    >
                        Submit
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Footer -->
        <div class="mt-8 text-center text-gray-500 text-xs">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('survey-form');
            const questions = document.querySelectorAll('.question-slide');
            const prevBtn = document.getElementById('prev-btn');
            const nextBtn = document.getElementById('next-btn');
            const submitBtn = document.getElementById('submit-btn');
            const progressBar = document.getElementById('survey-progress');
            const progressPercentage = document.getElementById('progress-percentage');
            const currentQuestionEl = document.getElementById('current-question');
            const checkboxRequired = document.querySelectorAll('.checkbox-required');
            
            let currentQuestion = 0;
            const totalQuestions = questions.length;
            
            // Update progress indicators
            function updateProgress() {
                const progress = ((currentQuestion + 1) / totalQuestions) * 100;
                progressBar.style.width = progress + '%';
                progressPercentage.textContent = Math.round(progress) + '%';
                currentQuestionEl.textContent = (currentQuestion + 1);
                
                // Show/hide navigation buttons
                if (currentQuestion > 0) {
                    prevBtn.classList.remove('hidden');
                } else {
                    prevBtn.classList.add('hidden');
                }
                
                if (currentQuestion === totalQuestions - 1) {
                    nextBtn.classList.add('hidden');
                    submitBtn.classList.remove('hidden');
                } else {
                    nextBtn.classList.remove('hidden');
                    submitBtn.classList.add('hidden');
                }
            }
            
            // Show a specific question
            function showQuestion(index) {
                questions.forEach((q, i) => {
                    if (i === index) {
                        q.classList.remove('hidden');
                    } else {
                        q.classList.add('hidden');
                    }
                });
                
                currentQuestion = index;
                updateProgress();
            }
            
            // Validate the current question
            function validateCurrentQuestion() {
                const currentSlide = questions[currentQuestion];
                const requiredInputs = currentSlide.querySelectorAll('[required]');
                let isValid = true;
                
                requiredInputs.forEach(input => {
                    if (input.type === 'radio') {
                        // For radio buttons, check if any in the group is checked
                        const name = input.name;
                        const checked = currentSlide.querySelector(`input[name="${name}"]:checked`);
                        if (!checked) {
                            isValid = false;
                            // Highlight the radio group
                            const radioGroup = currentSlide.querySelector(`input[name="${name}"]`).parentNode.parentNode;
                            radioGroup.classList.add('border-red-500', 'border', 'rounded', 'p-2');
                        }
                    } else if (input.value.trim() === '') {
                        isValid = false;
                        input.classList.add('border-red-500', 'ring-red-500');
                    }
                });
                
                // Check for checkbox groups that are required
                const checkboxGroups = currentSlide.querySelectorAll('.checkbox-required');
                checkboxGroups.forEach(hidden => {
                    const name = hidden.name.replace('_required', '');
                    const checkboxes = currentSlide.querySelectorAll(`input[name="${name}[]"]:checked`);
                    if (checkboxes.length === 0) {
                        isValid = false;
                        // Highlight the checkbox group
                        const checkboxGroup = currentSlide.querySelector(`input[name="${name}[]"]`).parentNode.parentNode;
                        checkboxGroup.classList.add('border-red-500', 'border', 'rounded', 'p-2');
                    }
                });
                
                return isValid;
            }
            
            // Event handler for Next button
            nextBtn.addEventListener('click', function() {
                if (validateCurrentQuestion()) {
                    showQuestion(currentQuestion + 1);
                }
            });
            
            // Event handler for Previous button
            prevBtn.addEventListener('click', function() {
                showQuestion(currentQuestion - 1);
            });
            
            // Initial setup
            showQuestion(0);
            
            // Remove validation styling on input
            document.querySelectorAll('input, textarea, select').forEach(element => {
                element.addEventListener('input', function() {
                    this.classList.remove('border-red-500', 'ring-red-500');
                    
                    // For radio and checkbox groups
                    if (this.type === 'radio' || this.type === 'checkbox') {
                        const group = this.parentNode.parentNode;
                        group.classList.remove('border-red-500', 'border', 'rounded', 'p-2');
                    }
                });
            });
            
            // Form submission validation
            form.addEventListener('submit', function(e) {
                if (!validateCurrentQuestion()) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
