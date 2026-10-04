<div align="center">

# InstantCart
<img width="1712" height="1595" alt="image" src="https://github.com/user-attachments/assets/3a3aa40b-d409-40b2-abda-f554bbd2559d" />


**Add to cart in the same frame as the click. For PrestaShop.**

![PrestaShop 1.7.6+](https://img.shields.io/badge/PrestaShop-1.7.6%2B-df0067?style=flat-square)
![PHP 7.1+](https://img.shields.io/badge/PHP-7.1%2B-777bb4?style=flat-square)
![No dependencies](https://img.shields.io/badge/dependencies-none-1baf7a?style=flat-square)
![Vanilla JS](https://img.shields.io/badge/JS-vanilla%2C%20~6%20kB%20gzipped-f6c343?style=flat-square)

<img src="docs/hero.svg" alt="Three quick clicks: the cart counts at once, the shop gets one request, a tick appears when it confirms" width="880">

**[▶ Play the demo](https://claude.ai/artifact/WXrHDFGAnimFAT1GvEosi1)** · or open [`docs/demo.html`](docs/demo.html) locally, no server needed

</div>

---

## The problem

A shopper clicks **Add to cart**. On a stock PrestaShop shop, nothing visible happens until two round trips finish:

1. the cart controller adds the product and presents the **whole cart** as JSON;
2. the cart module asks the server again to build its pop-up.

On a busy server, or a phone on a train, that is a second of a button doing nothing. People click again, and every extra click costs two more requests.

## What InstantCart does

The click is answered **immediately, in the browser**. The header count goes up, the button starts its small jar animation, a confirmation slides in. The shop is told in the background, through one lean request. If the shop says no (out of stock, minimum quantity), the count goes back and the reason is shown.

