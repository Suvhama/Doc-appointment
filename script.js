  function redirectToTopDoctors() {
            const searchTerm = document.getElementById('mainSearchInput').value.trim();
            if (searchTerm) {
                // Check if the search term contains "hospital" (case-insensitive)
                if (searchTerm.toLowerCase().includes('hospital')) {
                    window.location.href = `/html/bookAppointment.html?search=${encodeURIComponent(searchTerm)}`;
                } else {
                    window.location.href = `/html/topDoctor.html?search=${encodeURIComponent(searchTerm)}`;
                }
            } else {
                window.location.href = '/html/topDoctor.html';
            }
        }

        function toggleDropdown() {
            const dropdown = document.querySelector('.login-dropdown');
            const dropdownContent = document.querySelector('.dropdown-content');
            dropdown.classList.toggle('open');
            dropdownContent.classList.toggle('show');
        }

        const chatBody = document.getElementById('chatBody');
        const chatInput = document.getElementById('chatInput');
        const chatContainer = document.getElementById('chatContainer');
        let conversationState = {
            context: null,
            lastQuestion: null,
            userName: null
        };

        function loadChatHistory() {
            const history = JSON.parse(localStorage.getItem('chatHistory')) || [];
            history.forEach(msg => addMessage(msg.text, msg.type));
        }

        function saveMessage(text, type) {
            const history = JSON.parse(localStorage.getItem('chatHistory')) || [];
            history.push({
                text,
                type,
                timestamp: new Date().toISOString()
            });
            localStorage.setItem('chatHistory', JSON.stringify(history.slice(-50)));
        }

        function toggleChat() {
            chatContainer.classList.toggle('active');
            if (chatContainer.classList.contains('active') && chatBody.children.length <= 1) {
                loadChatHistory();
                if (!conversationState.userName) {
                    setTimeout(() => addMessage("May I have your name to assist you better?", "bot"), 500);
                }
            }
        }

        function addMessage(text, type) {
            const message = document.createElement('div');
            message.className = `chat-message ${type}`;
            message.innerHTML = text;
            chatBody.appendChild(message);
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        function showTypingIndicator() {
            const typing = document.createElement('div');
            typing.className = 'chat-message typing';
            typing.textContent = 'MediSync is typing';
            chatBody.appendChild(typing);
            chatBody.scrollTop = chatBody.scrollHeight;
            return typing;
        }

        function removeTypingIndicator(typingElement) {
            if (typingElement) typingElement.remove();
        }

        function sendMessage() {
            const message = chatInput.value.trim();
            if (!message) return;
            addMessage(message, 'user');
            saveMessage(message, 'user');
            chatInput.value = '';
            const typing = showTypingIndicator();
            setTimeout(() => {
                removeTypingIndicator(typing);
                const response = getBotResponse(message.toLowerCase());
                if (Array.isArray(response)) {
                    response.forEach((msg, index) => {
                        setTimeout(() => {
                            addMessage(msg, 'bot');
                            saveMessage(msg, 'bot');
                        }, index * 1000);
                    });
                } else {
                    addMessage(response, 'bot');
                    saveMessage(response, 'bot');
                }
            }, 1000);
        }

        function sendSuggestedMessage(message) {
            chatInput.value = message;
            sendMessage();
        }

        function getBotResponse(message) {
            const responses = {
                greeting: ['hello', 'hi', 'hey', 'greetings'],
                goodbye: ['bye', 'goodbye', 'see you', 'later'],
                appointment: ['book', 'appointment', 'schedule', 'visit'],
                consultation: ['consult', 'doctor', 'see a doctor', 'talk to doctor', 'meet doctor'],
                hours: ['hours', 'time', 'open', 'close', 'office hours'],
                contact: ['contact', 'support', 'help', 'reach'],
                payment: ['pay', 'payment', 'cost', 'insurance', 'method'],
                cancel: ['cancel', 'reschedule', 'change', 'modify'],
                thanks: ['thank', 'thanks', 'appreciate', 'grateful']
            };

            const detectedKeywords = [];
            for (const [category, keywords] of Object.entries(responses)) {
                if (keywords.some(keyword => message.includes(keyword))) {
                    detectedKeywords.push(category);
                }
            }

            if (conversationState.context === 'askingName' && !conversationState.userName) {
                conversationState.userName = message.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
                conversationState.context = null;
                return `Nice to meet you, ${conversationState.userName}! How can I assist you today?`;
            }

            if (detectedKeywords.length === 0) {
                return "I’m not sure how to help with that. Could you try asking something like 'How do I consult with a doctor?' or use the suggestions below?";
            }

            for (const keyword of detectedKeywords) {
                switch (keyword) {
                    case 'greeting':
                        return conversationState.userName ? `Hello again, ${conversationState.userName}! How can I help you?` : 'Hi there! How can I assist you today?';
                    case 'goodbye':
                        return conversationState.userName ? `Goodbye, ${conversationState.userName}! Take care.` : 'See you later! Feel free to chat again if you need help.';
                    case 'appointment':
                        conversationState.context = 'appointment';
                        conversationState.lastQuestion = message;
                        return ['To book an appointment, visit the "Book Appointment" page.', 'Select a doctor, choose a time slot, and confirm your booking.', 'Would you like to know more about the process?'];
                    case 'consultation':
                        conversationState.context = 'consultation';
                        conversationState.lastQuestion = message;
                        return ['You can consult with a doctor either in-person or via video consultation.', 'For in-person, use the "Book Appointment" page. For video, select "Video Consultation" from services.', 'Would you like steps for booking a video consultation?'];
                    case 'hours':
                        return 'Our office hours are Monday-Friday: 9:00 AM - 5:00 PM, Saturday: 10:00 AM - 2:00 PM, Sunday: Closed.';
                    case 'contact':
                        return 'You can reach us at <strong>support@medisync.com</strong> or call <strong>+977 1-1234567</strong>.';
                    case 'payment':
                        return 'We accept credit/debit cards, PayPal, and select insurance plans. Check the payment section during booking.';
                    case 'cancel':
                        conversationState.context = 'cancel';
                        return 'Yes, you can cancel or reschedule up to 24 hours before your appointment via your account dashboard. Need help with that?';
                    case 'thanks':
                        return conversationState.userName ? `You’re welcome, ${conversationState.userName}! Anything else I can do for you?` : 'My pleasure! How else can I assist?';
                }
            }

            if (conversationState.context === 'appointment' && (message.includes('yes') || message.includes('more'))) {
                return 'After selecting a slot, log in or sign up to finalize your booking. You’ll receive a confirmation email!';
            } else if (conversationState.context === 'consultation' && (message.includes('yes') || message.includes('steps') || message.includes('video'))) {
                return ['To book a video consultation, go to the "Video Consultation" section.', 'Choose a doctor, pick an available time, and ensure you have a stable internet connection.', 'You’ll get a link to join the call once confirmed!'];
            } else if (conversationState.context === 'cancel' && (message.includes('yes') || message.includes('help'))) {
                return 'Log into your account, go to "My Appointments," select the appointment, and choose "Cancel" or "Reschedule."';
            }

            return "I didn’t quite catch that. Could you clarify or ask something else?";
        }

        window.addEventListener('load', () => {
            if (!localStorage.getItem('chatHistory')) {
                addMessage("Welcome to MediSync Chat! How can I assist you today?", "bot");
                saveMessage("Welcome to MediSync Chat! How can I assist you today?", "bot");
            }
        });