# instantcart – instant add to cart (PrestaShop 1.7.6+)

The stock "Add to cart" waits for two full page builds before anything happens: the cart controller
presents the whole cart for its JSON answer, then ps_shoppingcart builds its pop-up in a second
request.

With this module:
- the click is answered at once – the header count goes up, the button gives a tick and a small
  confirmation slides in ("Dodano do koszyka · Koszyk");
- the product goes to `module/instantcart/add` in the background: checks, `Cart::updateQty()`, one
  count query. No cart presenter, no page assets, no template variables, no pop-up request;
- the count is then set to what the server says; if the server refuses (stock, minimum quantity)
  the count goes back and the reason is shown;
- composer products, required customisation and any failed request go through the shop's own
  add-to-cart (with its pop-up), so nothing gets lost.

- the button shows a line-drawn jar while the shop saves (solid once confirmed) and a ×N multiplier
  when the same product is added again; the confirmation shows the total;
- other modules can use the same instant add: `window.InstantCart.add(button, qty, send)`, where
  `send()` returns a promise of `{ok, count?, error?}`. The Infobia product composer uses it: its
  compositions are added without leaving the page.

Settings: on/off, and "Tell other modules" – sends the `updateCart` event after adding (one extra
request) for modules that need it, e.g. an analytics add_to_cart tag or a mini-cart dropdown.
