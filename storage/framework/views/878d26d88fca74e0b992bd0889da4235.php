

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto flex flex-col h-[calc(100vh-12rem)] min-h-[500px]">
    
    <!-- Chat Header -->
    <div class="bg-slate-900/80 border border-slate-800 backdrop-blur-xl rounded-t-2xl p-4 sm:p-5 flex items-center justify-between shadow-xl">
        <div class="flex items-center space-x-3">
            <div class="relative">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-purple-600 flex items-center justify-center text-white font-bold shadow-lg shadow-blue-500/20">
                    🤖
                </div>
                <span class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-slate-900 rounded-full"></span>
            </div>
            <div>
                <h1 class="text-base font-semibold text-white flex items-center gap-2">
                    DreamPC AI Hardware Assistant
                    <span class="text-[10px] bg-blue-500/10 text-blue-400 border border-blue-500/20 px-2 py-0.5 rounded-full font-medium uppercase tracking-wider">NLP Engine</span>
                </h1>
                <p class="text-xs text-slate-400">Ask about component compatibility, specs, or build advice</p>
            </div>
        </div>
        <div class="hidden sm:flex items-center text-xs text-slate-400 gap-1 bg-slate-800/60 px-3 py-1.5 rounded-lg border border-slate-700/50">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>Online & Ready</span>
        </div>
    </div>

    <!-- Messages Container -->
    <div id="chat-messages" class="flex-grow bg-slate-950/60 border-x border-slate-800/80 p-4 sm:p-6 overflow-y-auto space-y-4 shadow-inner">
        
        <!-- Welcome Message -->
        <div class="flex items-start space-x-3">
            <div class="w-8 h-8 rounded-lg bg-indigo-600/30 border border-indigo-500/40 text-indigo-400 flex items-center justify-center text-xs flex-shrink-0">
                🤖
            </div>
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl rounded-tl-none p-4 text-slate-200 text-sm max-w-[85%] shadow-md leading-relaxed">
                Hello! I am your AI Hardware Assistant. I can help you select optimal PC components, check Socket & TDP compatibility, or suggest custom builds. How can I assist your PC build today?
                <div class="mt-2 text-[10px] text-slate-500 text-right">System</div>
            </div>
        </div>

        <!-- Render Database Session Messages -->
        <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($msg->sender === 'user'): ?>
                <div class="flex items-start justify-end space-x-3">
                    <div class="bg-blue-600 text-white rounded-2xl rounded-tr-none p-4 text-sm max-w-[85%] shadow-lg shadow-blue-600/10 leading-relaxed">
                        <?php echo e($msg->message_text); ?>

                        <div class="mt-1 text-[10px] text-blue-200 text-right"><?php echo e($msg->created_at->format('H:i')); ?></div>
                    </div>
                    <div class="w-8 h-8 rounded-lg bg-blue-600/20 border border-blue-500/40 text-blue-400 flex items-center justify-center text-xs font-bold flex-shrink-0">
                        👤
                    </div>
                </div>
            <?php else: ?>
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600/30 border border-indigo-500/40 text-indigo-400 flex items-center justify-center text-xs flex-shrink-0">
                        🤖
                    </div>
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl rounded-tl-none p-4 text-slate-200 text-sm max-w-[85%] shadow-md leading-relaxed">
                        <?php echo nl2br(e($msg->message_text)); ?>

                        
                        <?php if(!empty($msg->json_payload['card_html'])): ?>
                            <?php echo $msg->json_payload['card_html']; ?>

                        <?php elseif(!empty($msg->json_payload['suggested_products'])): ?>
                            <div class="mt-3 pt-3 border-t border-slate-800/80 space-y-2">
                                <span class="text-xs font-semibold text-slate-400">Suggested Components:</span>
                                <div class="grid grid-cols-1 gap-2">
                                    <?php $__currentLoopData = $msg->json_payload['suggested_products']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="flex items-center justify-between bg-slate-950/80 border border-slate-800 px-3 py-2 rounded-lg text-xs">
                                            <span class="text-slate-200 font-medium"><?php echo e($item['name']); ?></span>
                                            <span class="text-emerald-400 font-bold"><?php echo e($item['price']); ?></span>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-2 text-[10px] text-slate-500 text-right"><?php echo e($msg->created_at->format('H:i')); ?></div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <!-- Quick Prompt Pills -->
    <div class="bg-slate-950 border-x border-t border-slate-800/80 px-4 py-2.5 flex items-center gap-2 overflow-x-auto text-xs no-scrollbar">
        <span class="text-slate-500 text-[11px] font-medium flex-shrink-0">Suggested:</span>
        <button type="button" onclick="sendQuickPrompt('Build a high performance gaming PC under $1500')" class="whitespace-nowrap bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 px-3 py-1.5 rounded-full transition hover:text-white hover:border-blue-500/40">
            💡 Build gaming PC under $1500
        </button>
        <button type="button" onclick="sendQuickPrompt('How does the compatibility engine work?')" class="whitespace-nowrap bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 px-3 py-1.5 rounded-full transition hover:text-white hover:border-blue-500/40">
            ⚙️ Compatibility Engine check
        </button>
        <button type="button" onclick="sendQuickPrompt('Recommend a GPU for 1440p gaming')" class="whitespace-nowrap bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 px-3 py-1.5 rounded-full transition hover:text-white hover:border-blue-500/40">
            🎮 1440p GPU advice
        </button>
    </div>

    <!-- Chat Input Area -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-b-2xl p-3 sm:p-4 shadow-2xl backdrop-blur-xl">
        <form id="chat-form" class="flex items-center space-x-2">
            <?php echo csrf_field(); ?>
            <input type="text" id="message-input" placeholder="Type your message or ask for build advice..." 
                   class="flex-grow bg-slate-950/90 border border-slate-800 text-white text-sm rounded-xl px-4 py-3 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-slate-500 transition" required>
            <button type="submit" id="send-btn" 
                    class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-3 rounded-xl font-medium text-sm transition shadow-lg shadow-blue-600/20 flex items-center space-x-2 flex-shrink-0 disabled:opacity-50">
                <span>Send</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chat-form');
    const messageInput = document.getElementById('message-input');
    const sendBtn = document.getElementById('send-btn');
    const chatMessages = document.getElementById('chat-messages');

    // Auto-scroll to bottom of chat
    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    scrollToBottom();

    // Send Quick Prompt
    window.sendQuickPrompt = function(text) {
        messageInput.value = text;
        chatForm.dispatchEvent(new Event('submit'));
    };

    // AJAX Form Listener
    chatForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const text = messageInput.value.trim();
        if (!text) return;

        // Clear input & disable submit
        messageInput.value = '';
        sendBtn.disabled = true;

        const currentTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        // Append User Bubble Instantly
        const userBubbleHtml = `
            <div class="flex items-start justify-end space-x-3 animate-fade-in">
                <div class="bg-blue-600 text-white rounded-2xl rounded-tr-none p-4 text-sm max-w-[85%] shadow-lg shadow-blue-600/10 leading-relaxed">
                    ${escapeHtml(text)}
                    <div class="mt-1 text-[10px] text-blue-200 text-right">${currentTime}</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-blue-600/20 border border-blue-500/40 text-blue-400 flex items-center justify-center text-xs font-bold flex-shrink-0">
                    👤
                </div>
            </div>
        `;
        chatMessages.insertAdjacentHTML('beforeend', userBubbleHtml);
        scrollToBottom();

        // Append Loading Spinner Bubble
        const spinnerId = 'spinner-' + Date.now();
        const spinnerHtml = `
            <div id="${spinnerId}" class="flex items-start space-x-3 animate-fade-in">
                <div class="w-8 h-8 rounded-lg bg-indigo-600/30 border border-indigo-500/40 text-indigo-400 flex items-center justify-center text-xs flex-shrink-0 animate-spin">
                    🌀
                </div>
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl rounded-tl-none p-4 text-slate-400 text-sm shadow-md flex items-center space-x-2">
                    <div class="flex space-x-1">
                        <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce [animation-delay:-0.15s]"></div>
                        <div class="w-2 h-2 bg-purple-500 rounded-full animate-bounce [animation-delay:-0.3s]"></div>
                    </div>
                    <span class="text-xs font-medium text-slate-400 ml-2">AI Assistant is processing...</span>
                </div>
            </div>
        `;
        chatMessages.insertAdjacentHTML('beforeend', spinnerHtml);
        scrollToBottom();

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch('/chat/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ message: text })
            });

            const data = await response.json();

            // Remove Loading Spinner
            document.getElementById(spinnerId)?.remove();

            if (data.status === 'success' && data.bot_message) {
                let cardHtml = '';
                if (data.bot_message.payload && data.bot_message.payload.card_html) {
                    cardHtml = data.bot_message.payload.card_html;
                } else if (data.bot_message.payload && data.bot_message.payload.suggested_products) {
                    const items = data.bot_message.payload.suggested_products.map(p => `
                        <div class="flex items-center justify-between bg-slate-950/80 border border-slate-800 px-3 py-2 rounded-lg text-xs">
                            <span class="text-slate-200 font-medium">${escapeHtml(p.name)}</span>
                            <span class="text-emerald-400 font-bold">${p.price}</span>
                        </div>
                    `).join('');
                    cardHtml = `
                        <div class="mt-3 pt-3 border-t border-slate-800/80 space-y-2">
                            <span class="text-xs font-semibold text-slate-400">Suggested Components:</span>
                            <div class="grid grid-cols-1 gap-2">${items}</div>
                        </div>
                    `;
                }

                const botBubbleHtml = `
                    <div class="flex items-start space-x-3 animate-fade-in">
                        <div class="w-8 h-8 rounded-lg bg-indigo-600/30 border border-indigo-500/40 text-indigo-400 flex items-center justify-center text-xs flex-shrink-0">
                            🤖
                        </div>
                        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl rounded-tl-none p-4 text-slate-200 text-sm max-w-[85%] shadow-md leading-relaxed">
                            ${escapeHtml(data.bot_message.text).replace(/\n/g, '<br>')}
                            ${cardHtml}
                            <div class="mt-2 text-[10px] text-slate-500 text-right">${data.bot_message.created_at}</div>
                        </div>
                    </div>
                `;
                chatMessages.insertAdjacentHTML('beforeend', botBubbleHtml);
            }
        } catch (error) {
            console.error(error);
            document.getElementById(spinnerId)?.remove();
            const errorHtml = `
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 rounded-lg bg-red-600/20 border border-red-500/40 text-red-400 flex items-center justify-center text-xs flex-shrink-0">
                        ⚠️
                    </div>
                    <div class="bg-red-500/10 border border-red-500/20 rounded-2xl rounded-tl-none p-4 text-red-300 text-sm">
                        Sorry, an error occurred while connecting to the assistant. Please try again.
                    </div>
                </div>
            `;
            chatMessages.insertAdjacentHTML('beforeend', errorHtml);
        } finally {
            sendBtn.disabled = false;
            scrollToBottom();
        }
    });

    function escapeHtml(str) {
        return str.replace(/&/g, "&amp;")
                  .replace(/</g, "&lt;")
                  .replace(/>/g, "&gt;")
                  .replace(/"/g, "&quot;")
                  .replace(/'/g, "&#039;");
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Arfa\Desktop\New folder (2)\DreamPC\resources\views/chat/index.blade.php ENDPATH**/ ?>