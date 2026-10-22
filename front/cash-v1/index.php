<?php
include("../clude/head.php");
?>

<body class="fixed-sn deep-orange-skin">
  <style>
    body {
      min-height: 100vh;
      background: linear-gradient(135deg, #fff7ec 0%, #fffdf9 100%);
      color: #5a402d;
    }
    .content {
        margin-left: 0px; /* */
        padding: 10px;
    }
}
    #content {
      padding: 10px;
    }
    
    .cash-hero {
      border: 1px solid #eadfce;
      border-radius: 10px;
      background: linear-gradient(135deg, #fff7ec 0%, #fffdf9 100%);
      padding: 22px 24px;
      box-shadow: 0 10px 24px rgba(92, 67, 48, 0.08);
      margin-bottom: 20px;
    }
    
    .cash-hero-label {
      font-size: 12px;
      font-weight: 900;
      letter-spacing: 0.12em;
      color: #9b6b33;
      text-transform: uppercase;
      margin-bottom: 8px;
    }
    
    .cash-hero h2 {
      margin: 0;
      font-weight: 900;
      color: #5b3d26;
      font-size: 28px;
    }
    
    .cash-hero p {
      margin: 8px 0 0;
      color: #7a6655;
      font-size: 14px;
    }
    
    .cash-container {
      display: flex;
      gap: 20px;
      align-items: flex-start;
      flex-wrap: wrap;
    }
    
    .cash-sidebar {
      width: 280px;
      display: grid;
      gap: 12px;
    }
    
    .cash-card {
      border: 1px solid #eddccc;
      border-radius: 10px;
      padding: 16px;
      background: #fff;
      font-weight: 800;
      color: #5a402d;
      box-shadow: 0 4px 12px rgba(92, 67, 48, 0.06);
      cursor: pointer;
      transition: all 0.3s ease;
      text-align: center;
      min-height: 80px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .cash-card:hover {
      background: linear-gradient(135deg, #fff8ef 0%, #fffdf9 100%);
      box-shadow: 0 6px 16px rgba(92, 67, 48, 0.12);
      transform: translateY(-2px);
    }
    
    .cash-card.active {
      background: linear-gradient(135deg, #fff8ef 0%, #fffdf9 100%);
      border-color: #d4a574;
    }
    
    .cash-content {
      flex: 1;
      min-width: 320px;
      border: 1px solid #eadfce;
      border-radius: 10px;
      background: rgba(255, 255, 255, 0.85);
      box-shadow: 0 10px 24px rgba(92, 67, 48, 0.05);
      min-height: 400px;
      padding: 20px;
    }
    
    .cash-content-header {
      font-size: 12px;
      font-weight: 900;
      letter-spacing: 0.12em;
      color: #9b6b33;
      text-transform: uppercase;
      margin-bottom: 16px;
    }
    
    .cash-content-body {
      color: #7a6655;
      font-size: 14px;
    }
    
    .cash-stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
      gap: 12px;
      margin-top: 16px;
    }
    
    .cash-stat {
      background: linear-gradient(135deg, #fff8ef 0%, #fffdf9 100%);
      border: 1px solid #eddccc;
      border-radius: 8px;
      padding: 12px;
      text-align: center;
    }
    
    .cash-stat-value {
      font-weight: 900;
      font-size: 18px;
      color: #5b3d26;
      margin: 4px 0;
    }
    
    .cash-stat-label {
      font-size: 11px;
      color: #9b6b33;
      font-weight: 700;
      text-transform: uppercase;
    }
    
    @media (max-width: 768px) {
      .cash-container {
        flex-direction: column;
      }
      
      .cash-sidebar {
        width: 100%;
        grid-template-columns: repeat(2, 1fr);
      }
      
      .cash-content {
        min-width: auto;
      }
      
      #content {
        padding: 16px;
      }
    }
  </style>

  <div id="content" class="content">
    <div class="cash-hero">
      <div class="cash-hero-label">Cashier Manager Only</div>
      <h2>Cash Management</h2>
      <p>Manage cash operations, expenses, and reconciliation.</p>
    </div>

    <div class="cash-container">
      <aside class="cash-sidebar">
        <div class="cash-card active" data-section="billpayment" data-load="bill/bills.php">
          <span>💰 Bill Payment</span>
        </div>
        <div class="cash-card" data-section="cashbox">
          <span>💰 Cash Box</span>
        </div>
        <div class="cash-card" data-section="expenses">
          <span>📊 Expenses</span>
        </div>
        <div class="cash-card" data-section="credit">
          <span>📝 Credit</span>
        </div>
        <div class="cash-card" data-section="reports">
          <span>📈 Reports</span>
        </div>
      </aside>

      <main class="cash-content" id="cash-content-area">
        <div style="text-align: center; padding: 40px; color: #9a7d5d;">
          <strong>Loading...</strong>
        </div>
      </main>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    // Section content for non-AJAX sections
    const staticSections = {
      cashbox: {
        header: 'Cash Box',
        description: 'Monitor your current cash position and daily transactions.',
        stats: [
          { label: "Today's Cash", value: 'KES 0.00' },
          { label: 'Expenses', value: 'KES 0.00' },
          { label: 'Collections', value: 'KES 0.00' },
          { label: 'Balance', value: 'KES 0.00' }
        ]
      },
      expenses: {
        header: 'Expenses',
        description: 'Track and manage cash expenses.',
        stats: [
          { label: 'Total', value: 'KES 0.00' },
          { label: 'Count', value: '0' },
          { label: 'Status', value: '--' }
        ]
      },
      credit: {
        header: 'Credit',
        description: 'Manage credit operations and customer accounts.',
        stats: [
          { label: 'Total', value: 'KES 0.00' },
          { label: 'Count', value: '0' },
          { label: 'Status', value: '--' }
        ]
      },
      reports: {
        header: 'Reports',
        description: 'View detailed cash reports and reconciliation.',
        stats: [
          { label: 'Total', value: 'KES 0.00' },
          { label: 'Count', value: '0' },
          { label: 'Status', value: '--' }
        ]
      }
    };

    function loadStaticSection(section) {
      const contentArea = document.getElementById('cash-content-area');
      const sectionData = staticSections[section];
      
      if (!sectionData) return;

      let statsHtml = '';
      sectionData.stats.forEach(stat => {
        statsHtml += `
          <div class="cash-stat">
            <div class="cash-stat-label">${stat.label}</div>
            <div class="cash-stat-value">${stat.value}</div>
          </div>
        `;
      });

      contentArea.innerHTML = `
        <div class="cash-content-header">${sectionData.header}</div>
        <div class="cash-content-body">
          <p>${sectionData.description}</p>
          <div class="cash-stats-grid">
            ${statsHtml}
          </div>
        </div>
      `;
    }

    function loadAjaxSection(filePath) {
      const contentArea = document.getElementById('cash-content-area');
      contentArea.innerHTML = '<div style="text-align: center; padding: 40px; color: #9a7d5d;"><strong>Loading...</strong></div>';
      
      $.ajax({
        url: filePath,
        type: 'GET',
        dataType: 'html'
      })
      .done(function(data) {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = data;

        const scripts = Array.from(wrapper.querySelectorAll('script'));
        scripts.forEach(function(script) {
          script.parentNode.removeChild(script);
        });

        contentArea.innerHTML = wrapper.innerHTML;

        scripts.forEach(function(script) {
          if (script.src) {
            const loadedScript = document.createElement('script');
            loadedScript.src = script.src;
            loadedScript.async = false;
            document.body.appendChild(loadedScript);
            return;
          }

          if (script.textContent && script.textContent.trim()) {
            window.eval(script.textContent);
          }
        });
      })
      .fail(function() {
        contentArea.innerHTML = '<div class="alert alert-danger">Could not load this section. Please try again.</div>';
      });
    }

    document.querySelectorAll('.cash-card').forEach(card => {
      card.addEventListener('click', function() {
        // Remove active class from all cards
        document.querySelectorAll('.cash-card').forEach(c => c.classList.remove('active'));
        // Add active class to clicked card
        this.classList.add('active');
        
        const section = this.dataset.section;
        const loadPath = this.dataset.load;
        
        // Load content based on whether it's AJAX or static
        if (loadPath) {
          loadAjaxSection(loadPath);
        } else {
          loadStaticSection(section);
        }
      });
    });

    // Load the first section (Bill Payment) on page load
    document.addEventListener('DOMContentLoaded', function() {
      const firstCard = document.querySelector('.cash-card.active');
      if (firstCard) {
        firstCard.click();
      }
    });
  </script>
</body>
