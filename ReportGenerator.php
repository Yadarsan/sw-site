<?php
/**
 * ReportGenerator Service Class
 * 
 * Generates sales and inventory reports
 */
class ReportGenerator {
    // Database connection and dependencies
    private $conn;
    private $orderDAO;
    private $productDAO;
    private $inventoryManager;
    
    /**
     * Constructor
     *
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
        $this->orderDAO = new OrderDAO($db);
        $this->productDAO = new ProductDAO($db);
        $this->inventoryManager = new InventoryManager($db);
    }
    
    /**
     * Generate sales report
     *
     * @param string $period Time period (daily, weekly, monthly, yearly, all)
     * @return array Sales report data
     */
    public function generateSalesReport($period = 'monthly') {
        // Get basic sales metrics
        $totalSales = $this->orderDAO->getTotalSales($period);
        $orderCount = $this->orderDAO->getOrderCount($period);
        
        // Get recent orders
        $recentOrders = $this->orderDAO->getRecentOrders(10);
        
        // Calculate average order value
        $averageOrderValue = $orderCount > 0 ? $totalSales / $orderCount : 0;
        
        // Format time period for display
        $periodDisplay = $this->formatPeriodDisplay($period);
        
        // Prepare report data
        $report = [
            'title' => "Sales Report - {$periodDisplay}",
            'generated_at' => date('Y-m-d H:i:s'),
            'period' => $period,
            'period_display' => $periodDisplay,
            'metrics' => [
                'total_sales' => $totalSales,
                'order_count' => $orderCount,
                'average_order_value' => $averageOrderValue
            ],
            'recent_orders' => $recentOrders
        ];
        
        return $report;
    }
    
    /**
     * Generate inventory report
     *
     * @return array Inventory report data
     */
    public function generateInventoryReport() {
        // Get all products
        $allProducts = $this->productDAO->findAll(1000, 0); // Get up to 1000 products
        
        // Get low stock products
        $lowStockProducts = $this->inventoryManager->getLowStockProducts();
        
        // Calculate inventory metrics
        $totalProducts = count($allProducts);
        $totalStock = 0;
        $totalValue = 0;
        $outOfStockCount = 0;
        $lowStockCount = count($lowStockProducts);
        
        foreach($allProducts as $product) {
            $totalStock += $product['stock'];
            $totalValue += $product['stock'] * $product['price'];
            
            if($product['stock'] == 0) {
                $outOfStockCount++;
            }
        }
        
        // Get product categories
        $categories = $this->productDAO->getAllCategories();
        
        // Calculate stock by category
        $stockByCategory = [];
        $valueByCategory = [];
        
        foreach($categories as $category) {
            $stockByCategory[$category] = 0;
            $valueByCategory[$category] = 0;
        }
        
        foreach($allProducts as $product) {
            if(isset($stockByCategory[$product['category']])) {
                $stockByCategory[$product['category']] += $product['stock'];
                $valueByCategory[$product['category']] += $product['stock'] * $product['price'];
            }
        }
        
        // Prepare report data
        $report = [
            'title' => "Inventory Report",
            'generated_at' => date('Y-m-d H:i:s'),
            'metrics' => [
                'total_products' => $totalProducts,
                'total_stock' => $totalStock,
                'total_value' => $totalValue,
                'out_of_stock_count' => $outOfStockCount,
                'low_stock_count' => $lowStockCount
            ],
            'stock_by_category' => $stockByCategory,
            'value_by_category' => $valueByCategory,
            'low_stock_products' => $lowStockProducts
        ];
        
        return $report;
    }
    
    /**
     * Export report to specified format
     * In this implementation, we'll just return formatted HTML or CSV
     *
     * @param array $reportData Report data
     * @param string $format Export format (html, csv)
     * @return string Formatted report
     */
    public function exportReport($reportData, $format = 'html') {
        if($format === 'csv') {
            return $this->exportToCsv($reportData);
        }
        
        // Default to HTML
        return $this->exportToHtml($reportData);
    }
    
    /**
     * Export report to HTML format
     *
     * @param array $reportData Report data
     * @return string HTML content
     */
    private function exportToHtml($reportData) {
        $title = $reportData['title'];
        $generatedAt = $reportData['generated_at'];
        
        $html = '<div style="font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto;">';
        $html .= "<h1>{$title}</h1>";
        $html .= "<p>Generated at: {$generatedAt}</p>";
        
        // Metrics section
        $html .= '<div style="margin: 20px 0; padding: 15px; background-color: #f9f9f9; border-radius: 5px;">';
        $html .= '<h2>Key Metrics</h2>';
        $html .= '<div style="display: flex; flex-wrap: wrap; gap: 15px;">';
        
        foreach($reportData['metrics'] as $key => $value) {
            $formattedKey = ucwords(str_replace('_', ' ', $key));
            $formattedValue = is_numeric($value) ? ($key === 'total_value' || $key === 'average_order_value' ? '$' . number_format($value, 2) : number_format($value)) : $value;
            
            $html .= '<div style="flex: 1; min-width: 200px; background-color: white; padding: 15px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
            $html .= "<h3>{$formattedKey}</h3>";
            $html .= "<p style='font-size: 24px; font-weight: bold;'>{$formattedValue}</p>";
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        // Report-specific sections
        if(isset($reportData['recent_orders'])) {
            // Sales report
            $html .= '<div style="margin: 20px 0;">';
            $html .= '<h2>Recent Orders</h2>';
            $html .= '<table style="width: 100%; border-collapse: collapse;">';
            $html .= '<tr style="background-color: #f2f2f2;">';
            $html .= '<th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Order ID</th>';
            $html .= '<th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Date</th>';
            $html .= '<th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Customer</th>';
            $html .= '<th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Status</th>';
            $html .= '<th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Total</th>';
            $html .= '</tr>';
            
            foreach($reportData['recent_orders'] as $order) {
                $html .= '<tr>';
                $html .= "<td style='padding: 10px; border: 1px solid #ddd;'>{$order['order_id']}</td>";
                $html .= "<td style='padding: 10px; border: 1px solid #ddd;'>" . date('Y-m-d', strtotime($order['order_date'])) . "</td>";
                $html .= "<td style='padding: 10px; border: 1px solid #ddd;'>{$order['customer_name']}</td>";
                $html .= "<td style='padding: 10px; border: 1px solid #ddd;'>{$order['status']}</td>";
                $html .= "<td style='padding: 10px; border: 1px solid #ddd;'>$" . number_format($order['total_price'], 2) . "</td>";
                $html .= '</tr>';
            }
            
            $html .= '</table>';
            $html .= '</div>';
        }
        
        if(isset($reportData['low_stock_products'])) {
            // Inventory report
            
            // Stock by category section
            if(isset($reportData['stock_by_category'])) {
                $html .= '<div style="margin: 20px 0;">';
                $html .= '<h2>Stock by Category</h2>';
                $html .= '<table style="width: 100%; border-collapse: collapse;">';
                $html .= '<tr style="background-color: #f2f2f2;">';
                $html .= '<th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Category</th>';
                $html .= '<th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Stock Count</th>';
                $html .= '<th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Value</th>';
                $html .= '</tr>';
                
                foreach($reportData['stock_by_category'] as $category => $stock) {
                    $value = $reportData['value_by_category'][$category];
                    $html .= '<tr>';
                    $html .= "<td style='padding: 10px; border: 1px solid #ddd;'>{$category}</td>";
                    $html .= "<td style='padding: 10px; border: 1px solid #ddd;'>" . number_format($stock) . "</td>";
                    $html .= "<td style='padding: 10px; border: 1px solid #ddd;'>$" . number_format($value, 2) . "</td>";
                    $html .= '</tr>';
                }
                
                $html .= '</table>';
                $html .= '</div>';
            }
            
            // Low stock products section
            $html .= '<div style="margin: 20px 0;">';
            $html .= '<h2>Low Stock Products</h2>';
            
            if(count($reportData['low_stock_products']) > 0) {
                $html .= '<table style="width: 100%; border-collapse: collapse;">';
                $html .= '<tr style="background-color: #f2f2f2;">';
                $html .= '<th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Product ID</th>';
                $html .= '<th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Name</th>';
                $html .= '<th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Category</th>';
                $html .= '<th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Stock</th>';
                $html .= '<th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Price</th>';
                $html .= '</tr>';
                
                foreach($reportData['low_stock_products'] as $product) {
                    $html .= '<tr>';
                    $html .= "<td style='padding: 10px; border: 1px solid #ddd;'>{$product['product_id']}</td>";
                    $html .= "<td style='padding: 10px; border: 1px solid #ddd;'>{$product['name']}</td>";
                    $html .= "<td style='padding: 10px; border: 1px solid #ddd;'>{$product['category']}</td>";
                    $html .= "<td style='padding: 10px; border: 1px solid #ddd; " . ($product['stock'] == 0 ? 'color: red; font-weight: bold;' : '') . "'>{$product['stock']}</td>";
                    $html .= "<td style='padding: 10px; border: 1px solid #ddd;'>$" . number_format($product['price'], 2) . "</td>";
                    $html .= '</tr>';
                }
                
                $html .= '</table>';
            } else {
                $html .= '<p>No low stock products found.</p>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Export report to CSV format
     *
     * @param array $reportData Report data
     * @return string CSV content
     */
    private function exportToCsv($reportData) {
        $csvContent = "";
        
        // Add title and generated date
        $csvContent .= "\"{$reportData['title']}\"\n";
        $csvContent .= "\"Generated at\",\"{$reportData['generated_at']}\"\n\n";
        
        // Add metrics
        $csvContent .= "\"Key Metrics\"\n";
        foreach($reportData['metrics'] as $key => $value) {
            $formattedKey = ucwords(str_replace('_', ' ', $key));
            $csvContent .= "\"{$formattedKey}\",\"{$value}\"\n";
        }
        $csvContent .= "\n";
        
        // Report-specific sections
        if(isset($reportData['recent_orders'])) {
            // Sales report
            $csvContent .= "\"Recent Orders\"\n";
            $csvContent .= "\"Order ID\",\"Date\",\"Customer\",\"Status\",\"Total\"\n";
            
            foreach($reportData['recent_orders'] as $order) {
                $orderDate = date('Y-m-d', strtotime($order['order_date']));
                $csvContent .= "\"{$order['order_id']}\",\"{$orderDate}\",\"{$order['customer_name']}\",\"{$order['status']}\",\"{$order['total_price']}\"\n";
            }
            
            $csvContent .= "\n";
        }
        
        if(isset($reportData['low_stock_products'])) {
            // Inventory report
            
            // Stock by category section
            if(isset($reportData['stock_by_category'])) {
                $csvContent .= "\"Stock by Category\"\n";
                $csvContent .= "\"Category\",\"Stock Count\",\"Value\"\n";
                
                foreach($reportData['stock_by_category'] as $category => $stock) {
                    $value = $reportData['value_by_category'][$category];
                    $csvContent .= "\"{$category}\",\"{$stock}\",\"{$value}\"\n";
                }
                
                $csvContent .= "\n";
            }
            
            // Low stock products section
            $csvContent .= "\"Low Stock Products\"\n";
            
            if(count($reportData['low_stock_products']) > 0) {
                $csvContent .= "\"Product ID\",\"Name\",\"Category\",\"Stock\",\"Price\"\n";
                
                foreach($reportData['low_stock_products'] as $product) {
                    $csvContent .= "\"{$product['product_id']}\",\"{$product['name']}\",\"{$product['category']}\",\"{$product['stock']}\",\"{$product['price']}\"\n";
                }
            } else {
                $csvContent .= "\"No low stock products found.\"\n";
            }
        }
        
        return $csvContent;
    }
    
    /**
     * Format time period for display
     *
     * @param string $period Time period
     * @return string Formatted period
     */
    private function formatPeriodDisplay($period) {
        switch($period) {
            case 'daily':
                return 'Today (' . date('Y-m-d') . ')';
            case 'weekly':
                return 'This Week';
            case 'monthly':
                return 'This Month (' . date('F Y') . ')';
            case 'yearly':
                return 'This Year (' . date('Y') . ')';
            default:
                return 'All Time';
        }
    }
}