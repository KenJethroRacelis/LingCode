<?php
session_start();

// 1. Authorization Guard: Keep out unauthorized sessions and regular student users
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mod') {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Helpdesk & Console FAQ | Landlord Core Engine</title>
    <link rel="stylesheet" href="../style.css">
    
    <style>
        /* Sticky footer flex containment requirements */
        html, body {
            height: 100%;
            margin: 0;
        }

        body {
            display: flex;
            flex-direction: column;
            background: #f4f6f9;
            font-family: Arial, sans-serif;
        }

        /* Hero presentation branding block matching the administrative palette */
        .hero {
            background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);
            color: white;
            text-align: center;
            padding: 50px 20px;
            border-bottom: 4px solid #e67e22; /* Moderator specific orange dividing line */
        }

        .hero h1 {
            margin: 0;
            font-size: 2.5em;
            letter-spacing: 0.5px;
        }

        .hero p {
            margin-top: 12px;
            font-size: 1.1em;
            color: #bdc3c7;
            font-style: italic;
        }

        /* Content spacing wrappers */
        .container-helpdesk {
            width: 80%;
            max-width: 1000px;
            margin: 40px auto;
            flex: 1 0 auto;
        }

        .faq-card {
            background: white;
            padding: 24px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            border-left: 4px solid #2c3e50;
            transition: transform 0.2s;
        }

        .faq-card:hover {
            transform: translateX(4px);
            border-left-color: #e67e22; /* Highlights moderator orange on focus */
        }

        .faq-card h3 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .faq-card p {
            color: #4a5568;
            line-height: 1.6;
            margin: 0;
            font-size: 0.95rem;
        }

        .contact-box {
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            text-align: center;
            margin-top: 40px;
            border-top: 4px solid #e67e22;
        }

        .contact-box h2 {
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 12px;
        }

        .contact-box p {
            color: #718096;
            margin: 6px 0;
            font-size: 1rem;
        }

        footer {
            flex-shrink: 0;
            margin-top: auto;
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 15px;
        }
    </style>
</head>
<body>

<header>
    <a href="dashboard.php">
        <img src="../images/lingcode.png" alt="Dashboard" width="103" height="60" style="border-radius:4px;">
    </a>

    <nav>
        <a href="dashboard.php">Work Orders Queue</a>
        <a href="forum.php">Community Forum</a>
        <a href="helpdesk.php" style="color: #e67e22;">Helpdesk</a>
        <a href="account.php" style="border-left: 1px solid #7f8c8d; padding-left: 15px;">
            🏠 <?php echo htmlspecialchars($_SESSION['username']); ?>
        </a>
        <a href="../logout.php" style="color: #ff7675; margin-left: 15px; font-weight: bold;">Logout</a>
    </nav>
</header>

<section class="hero">
    <h1>Staff Helpdesk & Console FAQ</h1>
    <p>Administrative Knowledge Base — Operating your management console</p>

    <div class="ai-chat-widget" style="margin: 30px auto; max-width: 800px; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <h3 style="color: #2c3e50; margin-top: 0;">🤖 LingCode</h3>
        <p style="color: #7f8c8d; font-size: 0.9rem;">Got questions? We'd love to help!</p>
        
        <div style="margin-bottom: 15px;">
            <textarea id="ai-prompt-input" rows="3" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" placeholder="Type your query here (e.g., Help me draft a respectful update about the water leaking issue)..."></textarea>
        </div>
        
        <button type="button" id="btn-ask-ai" style="background: #e67e22; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; font-weight: bold; cursor: pointer;">Ask Local AI</button>
        
        <div id="ai-response-box" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-left: 4px solid #e67e22; border-radius: 4px; display: none; min-height: 40px; line-height: 1.5; color: #2c3e50;">
        </div>
    </div>
</section>

<div class="container-helpdesk">

    <div class="faq-card">
        <h3>How do I process a student's maintenance request?</h3>
        <p>
            On your main <strong>Work Orders Queue</strong> dashboard, click the blue **Respond** action item on any active card. You will be routed to the ticket management profile page where you can input detailed maintenance updates and alter tracking configurations.
        </p>
    </div>

    <div class="faq-card">
        <h3>What occurs when I select a workflow status?</h3>
        <p>
            Setting a ticket to <code>In Progress</code> informs the resident that maintenance dispatch or hardware acquisition is underway. Setting it to <code>Resolved</code> marks the physical execution as complete and archives the card away from your active work order workspace.
        </p>
    </div>

    <div class="faq-card">
        <h3>How does the "Pin to Board" mechanic operate?</h3>
        <p>
            Pinning an item hoists that specific card directly to the top layer of your active console pipeline header. This allows your team to keep critical, widespread issues (e.g., building-wide water or power failures) anchored visually in focus regardless of entry age.
        </p>
    </div>

    <div class="faq-card">
        <h3>Can I toggle board pins directly from the public forum view?</h3>
        <p>
            Yes. In your <strong>Community Forum</strong> view, every public ticket card features an inline utility toggle labeled **Pin to Dashboard**. Clicking this instantly hooks or unhooks the card relative to your active queue, processed cleanly via your global action handler background script.
        </p>
    </div>

    <div class="faq-card">
        <h3>Why can't I see specific room maintenance files in the Community Forum view?</h3>
        <p>
            If a student files an order with the visibility checkbox unticked, the ticket data is cataloged exclusively as <code>private</code>. Those entries will stream securely into your private dashboard queue for processing, but are hidden entirely from the public forum space.
        </p>
    </div>

    <div class="faq-card">
        <h3>Are actions taken on this console immediately visible to students?</h3>
        <p>
            Yes. The moment you submit a progress report or switch a tracking status via your configuration forms, the variables commit immediately onto the data tables. Residents will observe the modifications live upon viewing their respective accounts.
        </p>
    </div>

    <div class="contact-box">
        <h2>System Engineering Support</h2>
        <p>If you encounter systematic database bottlenecks or authentication errors, please interface with the development infrastructure team.</p>
        <p style="font-weight: bold; color: #2c3e50; margin-top: 15px;">📧 System Admin: techops@lingcode.gchportal.edu.ph</p>
    </div>

</div>

<footer>
    <p>&copy; 2026 LingCode | GCH Service Request Portal | Landlord Core Engine</p>
</footer>

<script>
    document.getElementById('btn-ask-ai').addEventListener('click', async function() {
        const promptInput = document.getElementById('ai-prompt-input');
        const responseBox = document.getElementById('ai-response-box');
        const promptText = promptInput.value.trim();
        
        if (!promptText) {
            alert('Please enter a prompt first.');
            return;
        }
        
        // UI Visual feedback while processing heavy local computation
        responseBox.style.display = 'block';
        responseBox.style.borderColor = '#7f8c8d';
        responseBox.innerHTML = '<em>Thinking... (Running local model instance)</em>';
        this.disabled = true;
        
        try {
            // Send request payload straight to our standalone ask-ai handler path
            const response = await fetch('../ask-ai.php', { // Adjust path relative to your user/ folder structure
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ prompt: promptText })
            });
            
            const data = await response.json();
            
            if (data.error) {
                responseBox.style.borderColor = '#d63031';
                responseBox.innerHTML = `<span style="color: #d63031;"><strong>Error:</strong> ${data.error}</span>`;
            } else {
                responseBox.style.borderColor = '#e67e22';
                responseBox.innerHTML = `<strong>AI Response:</strong><br>${data.response.replace(/\n/g, '<br>')}`;
            }
        } catch (err) {
            responseBox.style.borderColor = '#d63031';
            responseBox.innerHTML = `<span style="color: #d63031;"><strong>System Error:</strong> Network connection to backend broken.</span>`;
        } finally {
            this.disabled = false;
        }
    });
</script>

</body>
</html>