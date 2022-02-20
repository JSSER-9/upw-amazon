<?php
namespace App\Libraries;

use DateTime;
use \App\Models\FbaInventory;
use \App\Models\FinancialDetials;
use \App\Models\Order;
use \App\Models\OrderCharges;
use \App\Models\OrderItem;
use \App\Models\PendingReport;
use \App\Models\Product;
use \App\Models\Shipment;
use \App\Models\ProductImage;

class DatabaseLayer
{
    /**
     * model
     * @var string
     */
    protected $model = null;

    protected function saveOrUpdateOrder($order)
    {
        $om = Order::where(['marketplace' => $order['marketplace'], 'orderId' => $order['orderId']])->first();
        if ($om) {
            $order = json_decode(json_encode($order), true);
            $om->update($order);
        } else {
            $orderModel = new Order($order);
            $orderModel->save();
        }
    }

    protected function saveOrUpdateShipment($shipment)
    {
        $shipmentModel = Shipment::where(['marketplace' => $shipment['marketplace'], 'ShipmentId' => $shipment['ShipmentId'], 'SellerSKU' => $shipment['SellerSKU']])->first();
        if ($shipmentModel) {
            $shipment = json_decode(json_encode($shipment), true);
            $shipmentModel->update($shipment);
        } else {
            $shipmentModel = new Shipment($shipment);
            $shipmentModel->save();
        }
    }

    protected function saveOrUpdateFbaInventory($inventory)
    {

        $fbaInventoryModel = FbaInventory::where(['marketplace' => $inventory['marketplace'], 'fnSku' => $inventory['fnSku']])->first();
        if ($fbaInventoryModel) {
            $inventory = json_decode(json_encode($inventory), true);
            $fbaInventoryModel->update($fbaInventoryModel["id"], $inventory);
        } else {
            $fbaInventory = new FbaInventory($inventory);
            $fbaInventory->save();
        }
    }

    protected function fetchPendingOrderItems()
    {
        $orderModel = new Order();
        $om = $orderModel->where('orderItemsSynced', 0)->limit(10)->get();
        return $om;
    }

    protected function fetchPendingOrderAddress()
    {
        $orderModel = new Order();
        $om = $orderModel->where('addressSynced', 0)->limit(10)->get();
        return $om;
    }

    protected function fetchPendingFinancialEvents()
    {
        $orderModel = new Order();
        $om = $orderModel->where('financialEventsSynced', 0)->limit(10)->get();
        return $om;
    }

    protected function saveFinancialDetails($data)
    {
        return FinancialDetials::firstOrCreate([
            'order_id' => $data["order_id"],
            'order_item_id' => $data["order_item_id"],
        ], $data);
    }

    protected function saveOrderCharge($data)
    {
        OrderCharges::create($data);
    }

    protected function fetchPendingOrderBuyer()
    {
        $orderModel = new Order();
        $om = $orderModel->where('buyerSynced', 0)->limit(10)->get();
        return $om;
    }

    protected function saveOrderItems($order, $orderItems)
    {
        OrderItem::where('marketplace', $order['marketplace'])->where('orderId', $order['orderId'])->delete();
        foreach ($orderItems as $item) {
            $orderItemModel = new OrderItem($item);
            $orderItemModel->save($item);
        }
        $order->orderItemsSynced = 1;
        $order->save();
        return true;
    }

    /**
     * Saving Data to table
     */
    protected function saveData($data)
    {
        $model = "\\App\\Models\\" . $this->reportConfig->modelName;
        $this->model = new $model();

        foreach ($data as $d) {
            if ($this->reportConfig->caseConvertField) {
                $convertedFields = array();
                foreach ($d as $key => $val) {
                    $key = strtolower($key);
                    $key = str_replace(' ', '-', $key);
                    $convertedFields[$key] = $val;
                }
                $d = $convertedFields;
            }
            if ($this->reportConfig->uniqueId) {
                $unique = array();
                foreach ($this->reportConfig->uniqueId as $u) {
                    $unique[$u] = $d[$u];
                }
                if (!empty($d["order-date"])) {
                    $d["order-date"] = DateTime::createFromFormat('d-M-Y', $d["order-date"])->format('Y-m-d');
                }
                if (!empty($d["return-request-date"])) {
                    $d["return-request-date"] = DateTime::createFromFormat('d-M-Y', $d["return-request-date"])->format('Y-m-d');
                }
                $this->model->updateOrCreate($unique, $d);
            } else {
                if ($d["Shipment Date"]) {
                    $d["Shipment Date"] = DateTime::createFromFormat('d/m/y H:i', $d["Shipment Date"]);
                } else {
                    $d["Shipment Date"] = null;
                }

                if ($d["Order Date"]) {
                    $d["Order Date"] = DateTime::createFromFormat('d/m/y H:i', $d["Order Date"]);
                } else {
                    $d["Order Date"] = null;
                }

                if ($d["Invoice Date"]) {
                    $d["Invoice Date"] = DateTime::createFromFormat('d/m/y H:i', $d["Invoice Date"]);
                } else {
                    $d["Invoice Date"] = null;
                }

                $this->model->insert($d);
            }
        }

        return true;
    }

    protected function saveReportQueue($reportId, $userMarketPlaceId, $reportName, $modelName)
    {
        if ($userMarketPlaceId) {
            PendingReport::firstOrCreate([
                'report_id' => $reportId,
                'user_marketplace_id' => $userMarketPlaceId,
            ], ['model_name' => $modelName, 'report_name' => $reportName]);
        }
    }

    protected function saveProducts($catlog)
    {
        return Product::firstOrCreate([
            'tenant_id' => $catlog['tenant_id'],
            'asin' => $catlog['asin'],
        ], $catlog)->id;
    }

    protected function saveProductImages($productImages, $tenantId, $asin)
    {
        ProductImage::where([
            'tenant_id' => $tenantId,
            'asin' => $asin,
        ])->delete();
        ProductImage::insert($productImages);
    }

    protected function deleteReportQueueIfExist($reportId, $userMarketPlaceId)
    {
        if ($userMarketPlaceId) {
            PendingReport::where(array('report_id' => $reportId, 'user_marketplace_id' => $userMarketPlaceId))->delete();
        }
    }
}
