document.addEventListener('DOMContentLoaded', function() {
    const chatbotBtn = document.getElementById('chatbotBtn');
    const chatbotContainer = document.getElementById('chatbotContainer');
    const closeChatbot = document.getElementById('closeChatbot');
    const chatbotMessages = document.getElementById('chatbotMessages');
    const chatbotInput = document.getElementById('chatbotInput');
    const sendChatbotMessage = document.getElementById('sendChatbotMessage');
    
    // Create notification badge
    const notificationBadge = document.createElement('span');
    notificationBadge.className = 'notification-badge';
    notificationBadge.textContent = '1';
    notificationBadge.style.display = 'none';
    chatbotBtn.appendChild(notificationBadge);
    
    // Toggle chatbot visibility
    chatbotBtn.addEventListener('click', function() {
        chatbotContainer.classList.add('active');
        chatbotBtn.classList.add('active');
        notificationBadge.style.display = 'none';
        setTimeout(() => {
            chatbotBtn.style.display = 'none';
        }, 300);
        chatbotInput.focus();
    });
    
    closeChatbot.addEventListener('click', function() {
        chatbotContainer.classList.remove('active');
        chatbotBtn.style.display = 'flex';
        setTimeout(() => {
            chatbotBtn.classList.remove('active');
        }, 10);
    });
    
    // Send message functionality
    sendChatbotMessage.addEventListener('click', sendMessage);
    chatbotInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
    
    // Initial greeting with delay
    setTimeout(() => {
        showNotificationBadge();
        setTimeout(() => {
            if (!chatbotContainer.classList.contains('active')) {
                addBotMessage("👋 Welcome to AsekosiGo! I'm your shopping assistant. Need help finding products or checking orders?");
            }
        }, 2000);
    }, 3000);
    
    function showNotificationBadge() {
        notificationBadge.style.display = 'flex';
        setTimeout(() => {
            if (!chatbotContainer.classList.contains('active')) {
                notificationBadge.style.animation = 'pulse 2s infinite';
            }
        }, 500);
    }
    
    function sendMessage() {
        const message = chatbotInput.value.trim();
        if (message) {
            addUserMessage(message);
            chatbotInput.value = '';
            showTypingIndicator();
            
            setTimeout(() => {
                removeTypingIndicator();
                processMessage(message);
            }, 800 + Math.random() * 1200);
        }
    }
    
    function addUserMessage(message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message user-message';
        messageDiv.innerHTML = formatMessage(message);
        chatbotMessages.appendChild(messageDiv);
        scrollToBottom();
    }
    
    function addBotMessage(message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message bot-message';
        messageDiv.innerHTML = formatMessage(message);
        chatbotMessages.appendChild(messageDiv);
        scrollToBottom();
    }
    
    function formatMessage(text) {
        // Convert URLs to clickable links
        text = text.replace(
            /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|]/ig, 
            '<a href="$&" target="_blank">$&</a>'
        );
        
        // Support for *bold* and _italic_ text
        text = text.replace(/\*(.*?)\*/g, '<strong>$1</strong>');
        text = text.replace(/\_(.*?)\_/g, '<em>$1</em>');
        
        // Convert line breaks to <br>
        return text.replace(/\n/g, '<br>');
    }
    
    function showTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'typing-indicator';
        typingDiv.id = 'typingIndicator';
        typingDiv.innerHTML = '<span></span><span></span><span></span>';
        chatbotMessages.appendChild(typingDiv);
        scrollToBottom();
    }
    
    function removeTypingIndicator() {
        const typingIndicator = document.getElementById('typingIndicator');
        if (typingIndicator) typingIndicator.remove();
    }
    
    function scrollToBottom() {
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }
    
    function processMessage(message) {
        const lowerMessage = message.toLowerCase();
        
        // Enhanced responses with quick replies
        if (/hello|hi|hey/.test(lowerMessage)) {
            addBotMessage("Hello there! 😊 How can I assist you today?<br><br>"
                + "• <a href='#' class='quick-reply'>Browse products</a><br>"
                + "• <a href='#' class='quick-reply'>Track my order</a><br>"
                + "• <a href='#' class='quick-reply'>Payment help</a>");
            return;
        }
        
        if (/help|support/.test(lowerMessage)) {
            addBotMessage("Here's what I can help with:<br><br>"
                + "🛍️ <strong>Product Search</strong>: Find items by category or keyword<br>"
                + "📦 <strong>Order Tracking</strong>: Check status of your purchases<br>"
                + "💳 <strong>Payments</strong>: Info about payment methods<br>"
                + "🔄 <strong>Returns</strong>: Start return process<br><br>"
                + "What would you like help with?");
            return;
        }
        
        if (/product|item|find|search|looking for/.test(lowerMessage)) {
            addBotMessage("I can help you find products! Try searching for:<br><br>"
                + "• \"<em>Show me smartphones under KES 30,000</em>\"<br>"
                + "• \"<em>Latest fashion trends</em>\"<br>"
                + "• \"<em>Home appliances on sale</em>\"<br><br>"
                + "Or browse our <a href='/client/products'>product catalog</a>.");
            return;
        }
        
        if (/order.*status|track.*order/.test(lowerMessage)) {
            addBotMessage("To check your order status:<br><br>"
                + "1. Go to <a href='/client/orders'>My Orders</a><br>"
                + "2. Select the order you want to track<br><br>"
                + "Need help with a specific order? Share your order number.");
            return;
        }
        
        if (/pay|payment|checkout|card|mpesa/.test(lowerMessage)) {
            addBotMessage("We accept:<br><br>"
                + "💳 <strong>Card Payments</strong> (Visa, Mastercard, Verve)<br>"
                + "📱 <strong>M-Pesa Mobile Money</strong><br><br>"
                + "All payments are secure with Paystack encryption.<br>"
                + "Having trouble? <a href='#'>Contact support</a>");
            return;
        }
        
        if (/thank|thanks|appreciate/.test(lowerMessage)) {
            addBotMessage("You're very welcome! 😊<br>Is there anything else I can help you with today?");
            return;
        }
        
        if (/bye|goodbye|see you/.test(lowerMessage)) {
            addBotMessage("Goodbye! 👋<br>Remember, I'm here whenever you need shopping help.");
            return;
        }
        
        // Default response with suggestions
        addBotMessage("I'm not sure I understand. Try asking:<br><br>"
            + "• \"<em>Where can I find laptops?</em>\"<br>"
            + "• \"<em>How do I track my order #12345?</em>\"<br>"
            + "• \"<em>What payment methods do you accept?</em>\"<br><br>"
            + "Or type <strong>help</strong> for more options.");
    }
    
    // Add quick reply functionality
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('quick-reply')) {
            e.preventDefault();
            const replyText = e.target.textContent;
            chatbotInput.value = replyText;
            sendMessage();
        }
    });
});