<!DOCTYPE html>
<html lang='en'>
	<head>
		<title>GraphLang Server App NOT FOUND</title>
		<meta charset='utf-8'>

        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                padding: 30px 20px;
                color: #333;
            }

            body::before {
                /*content: '404';*/
                content: 'GraphLang';
                position: fixed;
                top: 50%;
                left: 50%;
                /*transform: translate(-50%, -50%);*/
                /*font-size: 180px;*/
                font-weight: 900;
                color: rgba(255, 255, 255, 0.05);
                z-index: 0;
                pointer-events: none;

                transform: rotate(90deg);
                font-size: 120px;
            }

            body > * {
                position: relative;
                z-index: 1;
                max-width: 1024px;
                margin: 0 auto;
            }

            br {
                display: block;
                margin: 6px 0;
            }

            /* Style span headings */
            span {
                display: block;
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 1.2px;
                color: rgba(255, 255, 255, 0.95);
                margin: 24px 0 10px 0;
                padding: 8px 16px;
                background: rgba(255, 255, 255, 0.15);
                backdrop-filter: blur(10px);
                border-radius: 8px;
                border-left: 4px solid rgba(255, 255, 255, 0.5);
            }

            /* First heading */
            span:first-of-type {
                margin-top: 16px;
            }

            /* All ul lists */
            ul {
                list-style: none;
                background: white;
                border-radius: 12px;
                padding: 16px 20px;
                margin-bottom: 20px;
                box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            }

            /* List items */
            li {
                margin: 0;
                padding: 0;
                border-bottom: 1px solid #f0f0f0;
            }

            li:last-child {
                border-bottom: none;
            }

            /* Remove br inside li */
            li br {
                display: none;
            }

            /* Links */
            li a {
                display: block;
                padding: 10px 16px;
                color: #374151;
                text-decoration: none;
                font-size: 14px;
                font-weight: 500;
                transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
                border-radius: 8px;
                margin: 2px -6px;
                position: relative;
            }

            li a::before {
                content: '→';
                position: absolute;
                left: 10px;
                opacity: 0;
                transform: translateX(-8px);
                transition: all 0.25s ease;
                color: #667eea;
                font-weight: 700;
            }

            li a:hover {
                background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
                color: #667eea;
                padding-left: 32px;
                transform: translateX(4px);
            }

            li a:hover::before {
                opacity: 1;
                transform: translateX(0);
            }

            /* Empty href links (not implemented) */
            li a[href=""] {
                color: #9ca3af;
                cursor: not-allowed;
                opacity: 0.6;
            }

            li a[href=""]:hover {
                background: transparent;
                color: #9ca3af;
                padding-left: 16px;
                transform: none;
            }

            li a[href=""]::before {
                content: '✕';
                color: #9ca3af;
            }

            li a[href=""]:hover::before {
                opacity: 0.5;
            }

            /* Second ul - "Need to be implemented" */
            ul:nth-of-type(2) {
                background: #fffbeb;
                border: 2px dashed #fbbf24;
            }

            ul:nth-of-type(2) li {
                border-bottom-color: #fef3c7;
            }

            span:nth-of-type(2) {
                border-left-color: #fbbf24;
                background: rgba(251, 191, 36, 0.2);
            }

            /* Third ul - "Development stuff" */
            ul:nth-of-type(3) {
                background: #eff6ff;
                border: 2px dashed #3b82f6;
            }

            ul:nth-of-type(3) li {
                border-bottom-color: #dbeafe;
            }

            ul:nth-of-type(3) a {
                color: #1e40af;
            }

            ul:nth-of-type(3) a:hover {
                background: rgba(59, 130, 246, 0.1);
                color: #3b82f6;
            }

            ul:nth-of-type(3) a::before {
                color: #3b82f6;
            }

            span:nth-of-type(3) {
                border-left-color: #3b82f6;
                background: rgba(59, 130, 246, 0.2);
            }

            /* Responsive */
            @media (max-width: 1024px) {
                body {
                    padding: 20px 16px;
                }

                ul {
                    padding: 14px 18px;
                }

                li a {
                    padding: 9px 14px;
                    font-size: 13px;
                }

                span {
                    font-size: 11px;
                    padding: 6px 14px;
                }
            }

            /* Animation for page load */
            ul {
                animation: slideUp 0.4s ease forwards;
                opacity: 0;
            }

            span {
                animation: slideUp 0.4s ease forwards;
                opacity: 0;
            }

            span:nth-of-type(1) {
                animation-delay: 0.05s;
            }

            ul:nth-of-type(1) {
                animation-delay: 0.1s;
            }

            span:nth-of-type(2) {
                animation-delay: 0.15s;
            }

            ul:nth-of-type(2) {
                animation-delay: 0.2s;
            }

            span:nth-of-type(3) {
                animation-delay: 0.25s;
            }

            ul:nth-of-type(3) {
                animation-delay: 0.3s;
            }

            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(15px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>


	</head>
	<body>
        <span>
            <b>Not found controller.</b>
            <br/><br />
            <span>You can try:</span>
            <br/>
            <ul>
                <li><a href="?q=home">Home</a><br /></li>
                <li><a href="?q=registerUserViaEmail">Register User</a><br /></li>
                <li><a href="?q=userProjectList">User project list</a><br /></li>
                <li><a href="?q=createProject">Create project</a><br /></li>
                <li><a href="?q=userLoginForm">Login Form</a><br /></li>
                <li><a href="?q=logout">Logout</a><br /></li>
            </ul>

            <span>Need to be implemented:</span>
            <ul>
                <li><a href="">User Media, Libraries, Files...</a><br /></li>
            </ul>

            <span>Development stuff:</span>
            <ul>
                <li><a href="?q=experiment">Experiment View 1</a><br /></li>
                <li><a href="?q=loadNodesFromServer">Load nodes from server</a><br /></li>
            </ul>
        </span>
	</body>
</html>
