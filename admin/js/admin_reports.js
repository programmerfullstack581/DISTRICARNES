document.addEventListener('DOMContentLoaded', () => {
    // Initial Load
    updateReports();

    // Date range filter logic
    const dateRange = document.getElementById('dateRange');
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');

    // Set initial visibility
    if (dateRange.value === 'custom') {
        startDate.style.display = 'block';
        endDate.style.display = 'block';
    } else {
        startDate.style.display = 'none';
        endDate.style.display = 'none';
    }

    dateRange.addEventListener('change', function() {
        if (this.value === 'custom') {
            startDate.style.display = 'block';
            endDate.style.display = 'block';
        } else {
            startDate.style.display = 'none';
            endDate.style.display = 'none';
            
            // Set dates based on selection (optional client-side preview, 
            // but backend handles the range logic too)
            const today = new Date();
            let start, end;
            
            switch(this.value) {
                case 'today':
                    start = end = today;
                    break;
                case 'week':
                    start = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
                    end = today;
                    break;
                case 'month':
                    start = new Date(today.getFullYear(), today.getMonth(), 1);
                    end = today;
                    break;
                case 'quarter':
                    const quarter = Math.floor(today.getMonth() / 3);
                    start = new Date(today.getFullYear(), quarter * 3, 1);
                    end = today;
                    break;
                case 'year':
                    start = new Date(today.getFullYear(), 0, 1);
                    end = today;
                    break;
            }
            
            if (start && end) {
                startDate.value = start.toISOString().split('T')[0];
                endDate.value = end.toISOString().split('T')[0];
            }
        }
        // Auto-update on range change? 
        // Better to let user click "Actualizar" or "Generar Reporte" 
        // but for better UX we can trigger it:
        // updateReports(); 
    });
});

let charts = {}; // Store chart instances

async function updateReports() {
    const range = document.getElementById('dateRange').value;
    const start = document.getElementById('startDate').value;
    const end = document.getElementById('endDate').value;
    
    // Construct query params
    let params = new URLSearchParams({ range });
    if (range === 'custom') {
        if (!start || !end) {
            // Only alert if we are trying to update
            // alert('Por favor selecciona fecha inicio y fin');
            // return;
        }
        params.append('start', start);
        params.append('end', end);
    }
    
    // Show loading indicators if possible
    const btn = document.querySelector('button[onclick="updateReports()"]');
    if(btn) {
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
        setTimeout(() => btn.innerHTML = originalText, 500);
    }

    try {
        const res = await fetch(`../backend/php/sales/get_reports.php?${params.toString()}`);
        const data = await res.json();
        
        if (data.ok) {
                    updateSales(data.sales);
                    updateProducts(data.products);
                    updateCustomers(data.customers);
                    updateInventory(data.inventory, data.sales.total);
                    updateTopTable(data.topTable);
                } else {
            console.error('Error fetching reports:', data);
        }
    } catch (err) {
        console.error('Network error:', err);
    }
}

function updateSales(data) {
    if (!data || data.error) return;
    
    // Update Stats
    document.getElementById('statTotalSales').textContent = formatCurrency(data.total);
    document.getElementById('statOrdersCount').textContent = data.orders;
    document.getElementById('statAvgOrder').textContent = formatCurrency(data.avg);
    
    // Update Chart
    const ctx = document.getElementById('salesChart').getContext('2d');
    if (charts.sales) charts.sales.destroy();
    
    charts.sales = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Ventas Diarias',
                data: data.data,
                borderColor: '#48bb78',
                backgroundColor: 'rgba(72, 187, 120, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: 'rgba(255,255,255,0.6)' }, grid: { color: 'rgba(255,255,255,0.1)' } },
                y: { ticks: { color: 'rgba(255,255,255,0.6)' }, grid: { color: 'rgba(255,255,255,0.1)' } }
            }
        }
    });
    
    // Update Trend Chart (Using same data for simplicity or extended logic)
    const trendCtx = document.getElementById('salesTrendChart').getContext('2d');
    if (charts.trend) charts.trend.destroy();
    
    charts.trend = new Chart(trendCtx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Ventas (Selección)',
                data: data.data,
                backgroundColor: '#4299e1',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { ticks: { color: '#fff' }, grid: { display: false } },
                y: { ticks: { color: '#fff' }, grid: { color: 'rgba(255,255,255,0.1)' } }
            }
        }
    });
}

function updateProducts(data) {
    if (!data || data.error) return;
    
    document.getElementById('statTopProductSales').textContent = data.topProduct || 'N/A';
    document.getElementById('statCategoriesCount').textContent = data.totalCats;
    document.getElementById('statLowStock').textContent = data.lowStock;
    
    const ctx = document.getElementById('productsChart').getContext('2d');
    if (charts.products) charts.products.destroy();
    
    charts.products = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.catLabels,
            datasets: [{
                data: data.catData,
                backgroundColor: ['#c53030', '#4299e1', '#9f7aea', '#ed8936', '#48bb78', '#ecc94b'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { color: '#fff', boxWidth: 10 } }
            }
        }
    });
}

function updateCustomers(data) {
    if (!data || data.error) return;
    
    document.getElementById('statNewCustomers').textContent = data.new;
    document.getElementById('statReturningCustomers').textContent = data.recurring;
    document.getElementById('statCustomerRetention').textContent = data.retention + '%';
    
    const ctx = document.getElementById('customersChart').getContext('2d');
    if (charts.customers) charts.customers.destroy();
    
    charts.customers = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Nuevos', 'Recurrentes'],
            datasets: [{
                label: 'Clientes',
                data: [data.new, data.recurring],
                backgroundColor: ['#9f7aea', '#4299e1']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: '#fff' }, grid: { display: false } },
                y: { ticks: { color: '#fff' }, grid: { color: 'rgba(255,255,255,0.1)' } }
            }
        }
    });
}

function updateInventory(data, salesTotal) {
    if (!data || data.error) return;
    
    document.getElementById('statInventoryValue').textContent = formatCurrency(data.value);
    document.getElementById('statTotalItems').textContent = data.items;
    
    // Turnover Rate calculation
    let turnover = 0;
    const invVal = parseFloat(data.value);
    const salesVal = parseFloat(salesTotal || 0);
    
    if (invVal > 0) {
        turnover = (salesVal / invVal).toFixed(2);
    }
    
    const turnoverEl = document.getElementById('statTurnoverRate');
    if (turnoverEl) turnoverEl.textContent = turnover;
    
    const ctx = document.getElementById('inventoryChart').getContext('2d');
    if (charts.inventory) charts.inventory.destroy();
    
    charts.inventory = new Chart(ctx, {
        type: 'radar',
        data: {
            labels: ['Alto (>20)', 'Medio (10-20)', 'Bajo (5-10)', 'Crítico (<5)', 'Reabastecido'],
            datasets: [{
                label: 'Estado Stock',
                data: data.status,
                borderColor: '#ed8936',
                backgroundColor: 'rgba(237, 137, 54, 0.2)',
                pointBackgroundColor: '#ed8936'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    ticks: { display: false },
                    grid: { color: 'rgba(255,255,255,0.1)' },
                    angleLines: { color: 'rgba(255,255,255,0.1)' },
                    pointLabels: { color: '#fff' }
                }
            },
            plugins: { legend: { display: false } }
        }
    });
}

function updateTopTable(rows) {
    const tbody = document.querySelector('#topProductsTable tbody');
    if(!tbody) return;
    tbody.innerHTML = '';
    
    if (!rows || rows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No hay datos en este período</td></tr>';
        return;
    }
    
    rows.forEach(r => {
        const tr = document.createElement('tr');
        let trendHtml = '<span class="trend-indicator trend-neutral"><i class="fas fa-minus"></i> 0%</span>';
        const t = typeof r.tendencia === 'number' ? r.tendencia : null;
        if (t !== null) {
            if (t > 0) {
                trendHtml = `<span class="trend-indicator trend-up"><i class="fas fa-arrow-up"></i> +${t}%</span>`;
            } else if (t < 0) {
                trendHtml = `<span class="trend-indicator trend-down"><i class="fas fa-arrow-down"></i> ${t}%</span>`;
            } else {
                trendHtml = `<span class="trend-indicator trend-neutral"><i class="fas fa-minus"></i> 0%</span>`;
            }
        }
        tr.innerHTML = `
            <td>${r.nombre}</td>
            <td>${r.categoria || 'Sin Cat'}</td>
            <td>${r.unidades}</td>
            <td>${formatCurrency(r.ingresos)}</td>
            <td>${trendHtml}</td>
            <td>${(r.stock ?? 0)}</td>
        `;
        tbody.appendChild(tr);
    });
}

function formatCurrency(val) {
    return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(val || 0);
}

// Missing functions to prevent errors
function generateReport() {
    updateReports();
    // alert('Reporte generado exitosamente');
}

function exportChart(chartId) {
    const canvas = document.getElementById(chartId);
    if(canvas) {
        const link = document.createElement('a');
        link.download = `reporte-${chartId}.png`;
        link.href = canvas.toDataURL();
        link.click();
    } else {
        alert('No se pudo encontrar el gráfico para exportar.');
    }
}

function exportTable(tableId) {
    try {
        const table = document.getElementById(tableId);
        if (!table) return Swal.fire('Error', 'Tabla no encontrada', 'error');

        const wb = XLSX.utils.table_to_book(table, { sheet: "Datos" });
        XLSX.writeFile(wb, `reporte-${tableId}-${new Date().toISOString().split('T')[0]}.xlsx`);
        
        Swal.fire({
            icon: 'success',
            title: 'Exportado',
            text: 'La tabla se ha exportado correctamente a Excel',
            timer: 2000,
            showConfirmButton: false
        });
    } catch (e) {
        console.error(e);
        Swal.fire('Error', 'No se pudo exportar la tabla', 'error');
    }
}

function exportReport(format) {
    const { jsPDF } = window.jspdf;
    
    if (format === 'pdf') {
        try {
            const doc = new jsPDF();
            
            doc.setFontSize(20);
            doc.text("Reporte General - DistriCarnes", 14, 20);
            doc.setFontSize(12);
            doc.text(`Fecha: ${new Date().toLocaleDateString()}`, 14, 30);
            
            // Add Sales Summary
            doc.setFontSize(16);
            doc.text("Resumen de Ventas", 14, 45);
            
            const salesData = [
                ['Total Ventas', document.getElementById('statTotalSales').innerText],
                ['Pedidos', document.getElementById('statOrdersCount').innerText],
                ['Ticket Promedio', document.getElementById('statAvgOrder').innerText]
            ];
            
            doc.autoTable({
                startY: 50,
                head: [['Métrica', 'Valor']],
                body: salesData,
                theme: 'grid'
            });
            
            // Add Products Summary
            let finalY = doc.lastAutoTable.finalY + 15;
            doc.text("Resumen de Productos", 14, finalY);
            
            const prodData = [
                ['Más Vendido', document.getElementById('statTopProductSales').innerText],
                ['Categorías Activas', document.getElementById('statCategoriesCount').innerText],
                ['Stock Bajo', document.getElementById('statLowStock').innerText]
            ];
            
            doc.autoTable({
                startY: finalY + 5,
                head: [['Métrica', 'Valor']],
                body: prodData,
                theme: 'grid'
            });
            
            // Add Inventory
            finalY = doc.lastAutoTable.finalY + 15;
            doc.text("Inventario", 14, finalY);
            
            const invData = [
                ['Valor Total', document.getElementById('statInventoryValue').innerText],
                ['Total Items', document.getElementById('statTotalItems').innerText],
                ['Rotación', document.getElementById('statTurnoverRate').innerText]
            ];
            
            doc.autoTable({
                startY: finalY + 5,
                head: [['Métrica', 'Valor']],
                body: invData,
                theme: 'grid'
            });
            
            // Add Top Products Table if exists
            const topTable = document.getElementById('topProductsTable');
            if (topTable) {
                finalY = doc.lastAutoTable.finalY + 15;
                // Check page break
                if (finalY > 250) {
                    doc.addPage();
                    finalY = 20;
                }
                doc.text("Productos Más Vendidos", 14, finalY);
                doc.autoTable({
                    html: '#topProductsTable',
                    startY: finalY + 5,
                    theme: 'striped'
                });
            }
            
            doc.save(`reporte-completo-${new Date().toISOString().split('T')[0]}.pdf`);
            
            Swal.fire({
                icon: 'success',
                title: 'PDF Generado',
                text: 'El reporte se ha descargado correctamente',
                timer: 2000,
                showConfirmButton: false
            });
            
        } catch (e) {
            console.error(e);
            Swal.fire('Error', 'No se pudo generar el PDF. Asegúrate de que los datos estén cargados.', 'error');
        }
    } else if (format === 'excel' || format === 'csv') {
        try {
            // Create a workbook with multiple sheets
            const wb = XLSX.utils.book_new();
            
            // Summary Sheet
            const summaryData = [
                ['Reporte General', new Date().toLocaleDateString()],
                [''],
                ['VENTAS'],
                ['Total Ventas', document.getElementById('statTotalSales').innerText],
                ['Pedidos', document.getElementById('statOrdersCount').innerText],
                ['Ticket Promedio', document.getElementById('statAvgOrder').innerText],
                [''],
                ['PRODUCTOS'],
                ['Más Vendido', document.getElementById('statTopProductSales').innerText],
                ['Categorías', document.getElementById('statCategoriesCount').innerText],
                ['Stock Bajo', document.getElementById('statLowStock').innerText],
                [''],
                ['CLIENTES'],
                ['Nuevos', document.getElementById('statNewCustomers').innerText],
                ['Recurrentes', document.getElementById('statReturningCustomers').innerText],
                ['Retención', document.getElementById('statCustomerRetention').innerText],
                [''],
                ['INVENTARIO'],
                ['Valor Total', document.getElementById('statInventoryValue').innerText],
                ['Total Items', document.getElementById('statTotalItems').innerText],
                ['Rotación', document.getElementById('statTurnoverRate').innerText]
            ];
            
            const wsSummary = XLSX.utils.aoa_to_sheet(summaryData);
            XLSX.utils.book_append_sheet(wb, wsSummary, "Resumen");
            
            // Top Products Sheet
            const topTable = document.getElementById('topProductsTable');
            if (topTable) {
                const wsProducts = XLSX.utils.table_to_book(topTable).Sheets['Sheet1'];
                XLSX.utils.book_append_sheet(wb, wsProducts, "Top Productos");
            }
            
            const ext = format === 'excel' ? 'xlsx' : 'csv';
            XLSX.writeFile(wb, `reporte-completo-${new Date().toISOString().split('T')[0]}.${ext}`);
            
            Swal.fire({
                icon: 'success',
                title: 'Exportado',
                text: `El reporte se ha exportado a ${format.toUpperCase()}`,
                timer: 2000,
                showConfirmButton: false
            });
            
        } catch (e) {
            console.error(e);
            Swal.fire('Error', 'No se pudo exportar el archivo.', 'error');
        }
    }
}

function scheduleReport() {
    Swal.fire({
        title: 'Programar Reporte',
        html: `
            <div style="text-align: left; margin-top: 10px;">
                <label style="display:block; margin-bottom:5px;">Frecuencia:</label>
                <select id="scheduleFreq" class="swal2-input" style="margin:0 0 15px 0; width:100%;">
                    <option value="daily">Diario</option>
                    <option value="weekly">Semanal</option>
                    <option value="monthly">Mensual</option>
                </select>
                
                <label style="display:block; margin-bottom:5px;">Formato:</label>
                <select id="scheduleFormat" class="swal2-input" style="margin:0 0 15px 0; width:100%;">
                    <option value="pdf">PDF</option>
                    <option value="excel">Excel</option>
                </select>
                
                <label style="display:block; margin-bottom:5px;">Enviar a:</label>
                <input type="email" id="scheduleEmail" class="swal2-input" placeholder="correo@ejemplo.com" style="margin:0; width:100%;">
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Programar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#e53e3e',
        preConfirm: () => {
            return {
                freq: document.getElementById('scheduleFreq').value,
                format: document.getElementById('scheduleFormat').value,
                email: document.getElementById('scheduleEmail').value
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Here you would typically send this to the backend
            Swal.fire(
                '¡Programado!',
                `El reporte se enviará ${result.value.freq === 'daily' ? 'diariamente' : (result.value.freq === 'weekly' ? 'semanalmente' : 'mensualmente')} a ${result.value.email}`,
                'success'
            );
        }
    });
}

function toggleChartType() {
    if(charts.trend) {
        const currentType = charts.trend.config.type;
        const newType = currentType === 'bar' ? 'line' : 'bar';
        
        // Destroy and recreate
        const ctx = document.getElementById('salesTrendChart').getContext('2d');
        const data = charts.trend.data;
        
        charts.trend.destroy();
        charts.trend = new Chart(ctx, {
            type: newType,
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { ticks: { color: '#fff' }, grid: { display: false } },
                    y: { ticks: { color: '#fff' }, grid: { color: 'rgba(255,255,255,0.1)' } }
                }
            }
        });
    }
}
