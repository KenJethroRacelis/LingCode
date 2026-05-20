<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Helpdesk & FAQ | LingCode Portal</title>
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

        /* Hero presentation branding block matching your corporate palette */
        .hero {
            background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);
            color: white;
            text-align: center;
            padding: 50px 20px;
            border-bottom: 4px solid #d4af37; /* Branded gold dividing line */
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
            flex: 1 0 auto; /* Pushes everything beneath this downwards */
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
            border-left-color: #d4af37; /* Highlights gold on card focus interaction */
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
            border-top: 4px solid #d4af37;
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
            margin-top: auto; /* Locks to baseline edge */
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
        <a href="request.php">Submit a Request</a>
        <a href="forum.php">Community Forum</a>
        <a href="helpdesk.php" style="color: #d4af37;">Helpdesk</a>
        <a href="account.php" style="border-left: 1px solid #7f8c8d; padding-left: 15px;">
            📝 <?php echo htmlspecialchars($_SESSION['username']); ?>
        </a>
        <a href="..\logout.php" style="color: #ff7675; margin-left: 15px; font-weight: bold;">Logout</a>
    </nav>
</header>

<section class="hero">
    <h1>Helpdesk & FAQ</h1>
    <p>Naglilingkod sa iyong pagbukod — Navigating your dormitory assistance portal</p>

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
        <h3>How do I submit a new request?</h3>
        <p>
            Navigate over to the <strong>Submit a Request</strong> tab in your top navigation bar. From there, you can specify your request title, category, and location. The platform will immediately register your entry to the database.
        </p>
    </div>

    <div class="faq-card">
        <h3>What types of requests can I submit?</h3>
        <p>
            You can choose one among six types: 
            <strong>Electricity</strong>, <strong>Water</strong>, <strong>Internet</strong>, <strong>Sanitation</strong>, <strong>Security</strong>, and <strong>General</strong> logistical concerns.
        </p>
    </div>

    <div class="faq-card">
        <h3>Who can see my requests?</h3>
        <p>
            By default, all requests are <code>private</code>. You can click on the checkbox to make your post public and get it posted on the community forum! Both landlords and admins can see your requests regardless of visibility settings.
        </p>
    </div>

    <div class="faq-card">
        <h3>How can I verify the current standing of my request?</h3>
        <p>
            Progress towards your requests can be tracked in real-time via the <strong>Dashboard</strong> tab. Each request will have a status indicator that updates as it moves through the workflow stages: <code>Pending</code>, <code>In Progress</code>, and <code>Resolved</code>.    
        </p>
    </div>

    <div class="faq-card">
        <h3>What do the status indicators mean?</h3>
        <p>
            <code>Pending</code> indicates successful database cataloging awaiting administrative review. <code>In Progress</code> indicates the landlord has acknowledged the request, and <code>Resolved</code> means that a solution has been enacted for the request.
    </div>

    <div class="faq-card">
        <h3>Who can see the dashboard feed?</h3>
        <p>
            Users of all types can browse public posts on the <strong>Community Forum</strong>! However, only landlords and admins can see private requests in the dashboard feed. This is done to provide a good mix of privacy and community involvement.
        </p>
    </div>

    <div class="contact-box">
        <h2>Still need assistance?</h2>
        <p>If your specific technical challenge isn't documented above, please interface with the property management desk directly.</p>
        <p style="font-weight: bold; color: #2c3e50; margin-top: 15px;">📧 Email Address: support@lingcode.gchportal.edu.ph</p>
    </div>
</div>

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

<footer>
    <p>&copy; 2026 LingCode | GCH Service Request Portal | CpE-2204</p>
</footer>
</body>
</html>