<?php
/**
 * Lean add-to-cart endpoint: checks, Cart::updateQty(), product count. Nothing else.
 *
 * Unlike the shop's cart controller it does not present the whole cart for the answer, and unlike
 * a normal module page it skips the page setup (assets, template variables, displayHeader).
 * Answers {ok, count} | {ok:false, error} | {fallback:true} (= let the shop's own flow handle it).
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class InstantcartAddModuleFrontController extends ModuleFrontController
{
    public $ajax = true;
    public $content_only = true;
    public $display_header = false;
    public $display_footer = false;

    // nothing is rendered: skip the page's assets and template variables (they present the whole cart)
    public function setMedia()
    {
        return true;
    }

    public function initContent()
    {
    }

    public function postProcess()
    {
        header('Content-Type: application/json');
        header('Cache-Control: no-store');
        try {
            $answer = $this->add();
        } catch (\Throwable $e) {
            $answer = ['fallback' => true];
        }
        echo json_encode($answer);
        exit;
    }

    private function add()
    {
        if (!$this->isTokenValid() || Configuration::isCatalogMode()) {
            return ['fallback' => true];
        }
        $idProduct = (int) Tools::getValue('id_product');
        $qty = abs((int) Tools::getValue('qty', 1)) ?: 1;
        $idCustomization = (int) Tools::getValue('id_customization');
        $context = $this->context;
        $cart = $context->cart;

        $product = new Product($idProduct, false, $context->language->id);
        if (!Validate::isLoadedObject($product) || !$product->active || !$product->available_for_order
            || !$product->checkAccess((int) $cart->id_customer)) {
            return ['ok' => false, 'error' => $this->module->l('Ten produkt nie jest już dostępny.', 'add')];
        }

        $ipa = (int) Tools::getValue('id_product_attribute');
        $groups = Tools::getValue('group');
        if (!$ipa && is_array($groups) && $groups) {
            $ipa = (int) Product::getIdProductAttributeByIdAttributes($idProduct, $groups, true);
        }
        // a combination sent by the page must belong to this product
        if ($ipa && !Db::getInstance()->getValue('SELECT 1 FROM `' . _DB_PREFIX_ . 'product_attribute` WHERE id_product_attribute = ' . $ipa . ' AND id_product = ' . $idProduct)) {
            $ipa = 0;
        }
        if (!$ipa && $product->hasAttributes()) {
            $ipa = (int) Product::getDefaultAttribute($idProduct);
        }
        // required customisation not filled in: the product page's own flow explains it
        if (!$idCustomization && $product->customizable && !$product->hasAllRequiredCustomizableFields()) {
            return ['fallback' => true];
        }

        if (!$cart->id) {
            $cart->add();
            if (!$cart->id) {
                return ['fallback' => true];
            }
            $context->cookie->id_cart = (int) $cart->id;
            $context->cookie->write();
        }

        $done = $cart->updateQty($qty, $idProduct, $ipa ?: null, $idCustomization ?: false, 'up', 0, null, true, false);
        if ($done < 0) {
            $attr = class_exists('ProductAttribute') ? 'ProductAttribute' : 'Attribute';   // renamed in PrestaShop 8
            $min = $ipa ? (int) $attr::getAttributeMinimalQty($ipa) : (int) $product->minimal_quantity;

            return ['ok' => false, 'error' => sprintf($this->module->l('Minimalna ilość dla tego produktu to %d.', 'add'), max(1, $min))];
        }
        if (!$done) {
            return ['ok' => false, 'error' => $this->module->l('Nie ma już tyle sztuk tego produktu.', 'add')];
        }
        CartRule::autoRemoveFromCart($context);

        $count = (int) Db::getInstance()->getValue(
            'SELECT SUM(quantity) FROM `' . _DB_PREFIX_ . 'cart_product` WHERE id_cart = ' . (int) $cart->id
        );

        return ['ok' => true, 'count' => $count, 'id_product' => $idProduct, 'id_product_attribute' => $ipa, 'qty' => $qty];
    }
}
