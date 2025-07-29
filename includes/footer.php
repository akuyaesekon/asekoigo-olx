        </div> <!-- Close container div -->
        
        <footer class="bg-dark text-white mt-5 py-4">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h5><?php echo APP_NAME; ?></h5>
                        <p>Your trusted online marketplace for quality products.</p>
                    </div>
                    <div class="col-md-3">
                        <h5>Quick Links</h5>
                        <ul class="list-unstyled">
                            <li><a href="<?php echo APP_URL; ?>/client/index.php" class="text-white">Home</a></li>
                            <li><a href="<?php echo APP_URL; ?>/client/products/index.php" class="text-white">Products</a></li>
                            <li><a href="<?php echo APP_URL; ?>/client/login.php" class="text-white">Login</a></li>
                        </ul>
                    </div>
                    <div class="col-md-3">
                        <h5>Contact Us</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-envelope"></i> info@asekosigo.com</li>
                            <li><i class="fas fa-phone"></i> +254 700 000000</li>
                        </ul>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
                </div>
            </div>
        </footer>
        <!-- Modern Chatbot Button -->
<button class="chatbot-btn" id="chatbotBtn" aria-label="Open chat">
    <i class="fas fa-comment-alt"></i>
</button>

<!-- Chatbot Container -->
<div class="chatbot-container" id="chatbotContainer" aria-live="polite">
    <div class="chatbot-header">
        <h6><i class="fas fa-robot"></i> AsekosiGo Assistant</h6>
        <button class="close-chatbot" id="closeChatbot" aria-label="Close chat">
            &times;
        </button>
    </div>
    <div class="chatbot-messages" id="chatbotMessages" role="log"></div>
    <div class="chatbot-input">
        <input type="text" id="chatbotInput" placeholder="Type your message..." 
               aria-label="Type your message" autocomplete="off">
        <button id="sendChatbotMessage" aria-label="Send message">
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>
</div>
        
        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <!-- Custom JS -->
        <script src="<?php echo APP_URL; ?>/assets/js/script.js"></script>
        <script src="<?php echo APP_URL; ?>/assets/js/chatbot.js"></script>
    </body>
</html>