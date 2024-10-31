# ECom V2

Library to communicate with Resurs Bank APIs. This library intends to implement
complete API coverage.

**Please read the *"Notes about this documentation"* section below before proceeding.**

---

# Notes about this documentation

## Store ID

Whenever you see an argument named **storeId** in the documentation, it refers
to the unique identifier for the store in the Resurs Bank system tied to your
API account. The API account may have several stores.

## Http (controllers)

We include controllers in several of our **Modules** to simplify the process of
handling incoming requests. These controllers are meant to be used as a base
for your own controllers. In some cases they can function as is, but in most
cases you will need to extend them to fit your application.

You must also route incoming requests to these controllers, or to your own
controllers which extend them, since Ecom cannot know or affect how your
application routes incoming requests.

We will supply examples in this document of how you can incorporate these
controllers into your application. Please keep in mind that these examples are
simplified and may not fit your application without modification.

## Code samples

Any code example supplied by this document expects you to have executed the
*Config::setup()* method first. Also, most code examples require additional data
we do not have in advance, such as *store id* or *payment method configuration*.

Since our codebase is subject to change these examples should serve as 
**implementation guides** only.

## JavaScript constructors

The classes we provide in JavaScript all have constructors that accept an
**overrides** object. This object can contain functions which will override the
default behavior of the class. This is useful if you want to add custom error
. This is useful if you want to add custom error
handling, or if you want to modify the behavior of the class in some other way.

This applies to all JavaScript classes in the library.

## Actions applying to part of a payment (debit / credit / cancel / add)

When performing an action applying to part of a payment, for example a partial
capture or refund, it's important to understand how the API will handle the
payloads. Each **\Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine**
object submitted to Resurs Bank, regardless of API call, will create a new
matching object within the API.

This means that you can submit five **OrderLine** objects to the API when you
create the payment, and a single **OrderLine** object when you perform your
capture call.

The API will ensure that there are enough resources (money) available on the
payment for your API call to succeed, so you cannot capture / credit more than
the payment is worth.

It's very important to understand that there is no correlation, at all,
between the **OrderLine** objects submitted to the API in various API calls,
they are never mapped together in any way.

When using the **Merchant Portal** to view a payment, you will not see all the
individual **OrderLine** objects as separate rows on a payment. The GUI
will attempt to merge them together to give you a better overview of the
payment. It's again important to understand that this is only a visual aid,
the API will always treat each **OrderLine** object as a separate entity.

## Accounting file

Resurs Bank will at regular intervals submit a report containing information
about payments submitted from the bank to the client.

## Property and object names

The names of data models (Lib/Model/*) and the properties defined on these
models are named according to the Resurs Bank API. This is to make it easier
to understand the data models and how they relate to the API.

Note that *classnames* sometimes differ, when a sensible structure is not
possible using the names of the corresponding objects defined by the API. 
Property names will however always be identical to the API.

## Shadow limit

When creating a payment there will be an **authorizedAmount** and a
**approvedCreditLimit**.

* **authorizedAmount** - The amount that the customer **has agreed** to pay.
* **approvedCreditLimit** - The amount that the customer **is allowed** to pay.

These should not be confused with each other.

The point of **approvedCreditLimit** is to allow merchants to replace items
in a payment without the customer needing to re-approve the payment. This is
useful when a customer wants to replace an item with another item og slightly
greater value (like changing the colour option for a rubber chicken or similar).

**Note that functions written with the Ecom library will use *authorizedAmount*
as maximum value, we make no use of *approvedCreditLimit*.**

## Widget rendering

Widgets are special classes, always contained within a **Widget** subdirectory
of Module's when available.

Widgets are meant to simplify the process of rendering forms, buttons, and
information which relates to data from the API / integrates library
functionality such as displaying payment information or fetching customer
address information.

When we create an instance of a widget, it will render .phtml files (always in
context to the class instance, so all public data/methods available to the PHP
class will also be available to any template that widget renders). It can also
read SVGS / CSS files or assemble data in other ways. This data will then be
kept on properties of the widget instance, which can then be accessed by your
application to render the widget.

This example uses a hypothetical widget named **GetAddress**.

* HTML template (named **get-address.phtml**)
* JavaScript template (named **get-address.js.phtml**)
* CSS stylesheet (named **get-address.css**)

**Note that stylesheets are commonly not phtml files, since they render the same
way regardless of how the Widget class instance is configured.**

## Merchant Portal

The Merchant Portal when mentioned within this document refers to a service
supplied by Resurs Bank where merchant can view and manage payments.

When there is a problem with the integration supplied by this library most
actions can be performed manually within the Merchant Portal instead.

## Translations

The library contains translations for SE/NO/DK/FI/EN. You can pass a language
to the **Config::setup()** method to set the language for the library. This will
affect the language of messages and other text output from the library.

Some modules have individual *Translator* classes to handle content specific
translations. General translations are handled by the **Locale** library
(*src/Lib/Locale*).

Essentially this is how it works:

1. The *Translator* class will parse a *json* file containing translations into *Phrase* objects.
2. The json file consists of a structure where each key contains an object with texts in various languages.
3. The *Translator::translate()* method will resolve the correct text based on configured language and supplied id (key).

Assume we want to translate "Hello World", we would first add a segment to our
*translations.json* file:

```json
{
    "hello-world": {
        "SE": "Hej världen",
        "NO": "Hei verden",
        "DK": "Hej verden",
        "FI": "Hei maailma",
        "EN": "Hello world"
    }
}
```

Then we would call the *Translator::translate()* method with the id "hello-world":

```php
use Resursbank\Ecom\Config;

Config::setup(
    language: Language::SV
);

$translated = Translator::translate('hello-world'); // "Hej världen"
```

---

# Library developer setup

When setting up the project for development please be sure the parent directory
is not called **src** since this will break PHPCS configuration (meaning the
**src** directory of the project cannot be located at **src/src**).

When using PHPStorm, right-click the **tests** directory, select
"Mark Directory as" -> "Test Source". Remember to import code style and
inspections settings from qa/phpstorm.

Be sure to execute **./qa/setup** from your terminal and follow the steps to
setup all QA tools.

---

# Config

The **Config::setup()** must always be called before interacting with the
library, such as performing API calls or rendering widgets.

This method creates a configured instance of the ECom library with all necessary
information to execute API calls and perform related actions (such as logging,
caching, data persistence etc.). **Config** acts as a singleton and the instance
is stored in **Config::$instance**.

You could call **Config::setup()** before each interaction, or you could call it
at a central location in your application (such as the entry point) to ensure
that the configured instance is always available, simplifying interactions.

## Basic configuration

This is the smallest possible configuration to allow for API interaction. It
will also be enough for, almost, all widgets to function properly.

```php
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Api\Environment;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Api\GrantType;

Config::setup(
    jwtAuth: new Jwt(
        clientId: 'your-client-id',
        clientSecret: 'your-client-secret',
        scope: Scope::MOCK_MERCHANT_API,
        grantType: GrantType::CREDENTIALS,
    )
);
```

## Configuration options

Note that the properties described below can be accessed by getters on Config, like
`Config::getLogger()`.

### -#- logger

Logging handler, used to log information and errors. Accepts any class implementing **\Resursbank\Ecom\Lib\Log\LoggerInterface**.

Defaults to an instance of **\Resursbank\Ecom\Lib\Log\NoneLogger** which simply ignores logging. Alternative classes included with the library can be found in **\Resursbank\Ecom\Lib\Log**.

- \Resursbank\Ecom\Lib\Log\FileLogger - write log entries to file on disk. Accepts directory path as parameter.
- \Resursbank\Ecom\Lib\Log\StdoutLogger - write log entries to standard output.

### -#- cache

Cache handler, used to cache data. Accepts any class implementing **\Resursbank\Ecom\Lib\Cache\CacheInterface**.

Defaults to an instance of **\Resursbank\Ecom\Lib\Cache\None** which does not cache data. Alternative classes included with the library can be found in **\Resursbank\Ecom\Lib\Cache**.

- \Resursbank\Ecom\Lib\Cache\Filesystem - file-based cache handler. Accepts a directory path as parameter.
- \Resursbank\Ecom\Lib\Cache\Redis - Redis cache handler.

### -#- jwtAuth

JWT (JSON Web Token) authentication credentials. Accepts an instance of **\Resursbank\Ecom\Lib\Model\Network\Auth\Jwt**.

This is the **Client ID** and **Client Secret** provided by Resurs Bank. We use these to obtain tokens, which are used to authenticate API calls.

Defaults to `null` if not provided. Required for the library communicate with the API.

### -#- paymentHistoryDataHandler

Data handler for payment history. Accepts any class implementing **\Resursbank\Ecom\Module\PaymentHistory\DataHandler\DataHandlerInterface**.

Defaults to an instance of **\Resursbank\Ecom\Module\PaymentHistory\DataHandler\VoidDataHandler** which does nothing.

- \Resursbank\Ecom\Module\PaymentHistory\DataHandler\FileDataHandler - write entries to files on disk. Accepts file path as parameter.

A more common way to store payment history data would be to implement a custom data handler that stores the data in a database or similar.

### -#- logLevel

Log level for the logger.

Defaults to **\Resursbank\Ecom\Lib\Log\LogLevel::INFO**

### -#- userAgent

User agent string for network requests. 

Defaults to an empty string.

### -#- isProduction

Boolean flag indicating if the environment is production.

Defaults to `false`.

### -#- proxy

Proxy server address for network requests.

Defaults to an empty string.

### -#- proxyType

Type of proxy server. Accepts an integer.

Defaults to `0`.

### -#- timeout

Timeout for network requests in seconds. Accepts an integer.

Defaults to `0`.

### -#- language

The library contain translations for SE/NO/DK/FI/EN. This setting controls which language to use.

Defaults to **\Resursbank\Ecom\Lib\Locale\Language::EN**.

---

# Code

The code in the library is separated into three basic categories:

1. **Modules** - Independent pieces of functionality that are meant to be used
   by the end user. Modules are not allowed to communicate with each other to
   allow for maximum flexibility.
2. **Libraries** - Contain abstract classes, interfaces, enums as well as
   general business logic not associated with any one specific **Module**. Libraries are allowed to communicate with each other.
3. **Exceptions** - Custom exceptions that can be thrown by any part of the
   library and used by the end user to handle errors.

Each of these components are described in more detail below.

## Updates

The library is updated regularly. Please check the **CHANGELOG.md** file for
information on the latest updates. Only major versions may contain breaking
changes. Minor / patch versions are backwards compatible always.

## Testing

The library is tested using PHPUnit. The tests are located in the **tests**
directory and every part of the library which makes sense to test is covered by
tests.

## Code styling

The library is styled using the following tools and standards:

* PHP Code Sniffer (PHPCS) with modified PSR-12 standard.
* PHP Mess Detector (PHPMD).
* PHPStan.

Customized rulesets for each can be found under **qa**. Note that **src** and
**tests** directories in **qa** contain the rulesets for the library itself and
the tests respectively.

---

# Exceptions

*src/Exception*

Ecom supplies a list of custom exceptions that can be thrown by the library.
The list describes the current classes and their descriptions.

| Exception Class                | Description                                |
|--------------------------------|--------------------------------------------|
| ApiException                   | Base exception for all API exceptions.     |
| AttributeCombinationException  | Exception for attribute combination errors.|
| AttributeParameterException    | Exception for attribute parameter errors.  |
| AuthException                  | Exception for authentication errors.       |
| CacheException                 | Exception for cache errors.                |
| CallbackException              | Exception for callback errors.             |
| CallbackTypeException          | Exception for callback type errors.        |
| CollectionException            | Exception for collection errors.           |
| ConfigException                | Exception for configuration errors.        |
| CurlException                  | Exception for curl errors.                 |
| EventException                 | Exception for event errors.                |
| EventSubscriberException       | Exception for event subscriber errors.     |
| FilesystemException            | Exception for filesystem errors.           |
| GetAddressException            | Exception for get address errors.          |
| HttpException                  | Exception for HTTP errors.                 |
| IOException                    | Exception for IO errors.                   |
| MissingPaymentException        | Exception for missing payment errors.      |
| PaymentActionException         | Exception for payment action errors.       |
| PermissionException            | Exception for permission errors.           |
| SessionException               | Exception for session errors.              |
| SessionValueException          | Exception for session value errors.        |
| TestException                  | Exception for test errors.                 |
| TranslationException           | Exception for translation errors.          |
| UrlValidationException         | Exception for URL validation errors.       |
| ValidationException            | Exception for validation errors, see below.|
| WebhookException               | Exception for webhook errors.              |
| Validation/EmptyValueException | Value was not expected to be empty.        |
| Validation/FormatException                | Value was not in expected format.          |
| Validation/IllegalAttributeException      | Value contained illegal attributes.        |
| Validation/IllegalCharsetException        | Value contained illegal characters.        |
| Validation/IllegalCustomerException       | Value was not a valid customer.            |
| Validation/IllegalIpException             | Value was not a valid IP address.          |
| Validation/IllegalTypeException           | Value was not of expected type.            |
| Validation/IllegalUrlException            | Value was not a valid URL.                 |
| Validation/IllegalValueException          | Value was not allowed.                     |
| Validation/MissingKeyException            | Required key was not found in array.       |
| Validation/MissingValueException          | Required value was missing.                |
| Validation/NotJsonEncodedException        | Value was not JSON encoded.                |

---

# Libraries

*src/Lib*

Libraries contain abstract classes, interfaces, enums as well as general
business logic not associated with any one specific **Module**. Libraries are
allowed to communicate with each other, but may not communicate with modules.
This is important to maintain flexibility, modules are meant to be standalone
components, while libraries are meant to be shared across modules.

The list below describes the current libraries and their contents.

| Library     | Description                                                            |
|-------------|------------------------------------------------------------------------|
| Api         | General API classes and functionality.                                 |
| Attribute   | Handles attribute-related logic and operations.                        |
| Cache       | Contains cache implementations and interfaces.                         |
| Collection  | Abstract base class for all collection implementations.                |
| Http        | Handles HTTP communication and related functionality.                  |
| Locale      | Helps with localization and country availability.                      |
| Log         | Logging functionality and implementations.                             |
| Model       | Data models utilized by Modules.                                       |
| Network     | Network communication functionality and related classes.               |
| Order       | Manages order-related logic and operations.                            |
| Repository  | Contains abstract repository classes for data access and manipulation. |
| Utilities   | Generic functionality that does not belong to any specific library.    |
| Validation  | Functionality to help validate various kinds of data.                  |
| Widget      | Manages widget-related logic and operations.                           |

---

# Modules

*src/Module*

Modules are independent pieces of functionality that are meant to be used by the
end user. Modules are standalone components and are not allowed to communicate
with each other.

We will describe each module and its contents below in separate sections.

---

# [Module] Action

*src/Module/Action*

Interaction with the **payment action log**.

## Repository

*src/Module/Action/Repository*

### -#- \Resursbank\Ecom\Module\Action\Repository::getAction()

Get log for a single action performed against a payment.

```php
use \Resursbank\Ecom\Module\Action\Repository;

$action = Repository::getAction(
    paymentId: 'payment-id',
    actionId: 'action-id',
);
```

---

# [Module] AnnuityFactor

*src/Module/AnnuityFactor*

Payment plans. For example "$30/month for 12 months".

## Repository

*src/Module/AnnuityFactor/Repository*

Please note that **\Resursbank\Ecom\Module\AnnuityFactor\Repository::getCache()**
and **\Resursbank\Ecom\Module\AnnuityFactor\Repository::getApi()** are public
for testability. You are not expected to use these methods in your application.

### -#- \Resursbank\Ecom\Module\AnnuityFactor\Repository::getAnnuityFactors()

Get all annuity factors (payment plans) for a specific payment method.

```php
use \Resursbank\Ecom\Module\AnnuityFactor\Repository;

$annuityFactors = Repository::getAnnuityFactors(
    storeId: 'store-id',
    paymentMethodId: 'payment-method-id',
);
```

### -#- \Resursbank\Ecom\Module\AnnuityFactor\Repository::getMethods()

Filter supplied payment methods collection, returning only those that have
annuity factors.

```php
use \Resursbank\Ecom\Module\AnnuityFactor\Repository;
use \Resursbank\Ecom\Lib\Model\PaymentMethodCollection;

// The idea here is that you fetch a collection of payment methods from the API
// using the PaymentMethod module, and then filter it using this method.
$paymentMethods = Repository::getMethods(
    storeId: 'store-id',
    paymentMethods: new PaymentMethodCollection(data: []),
);
```

## [Widget] GetPeriods

*src/Module/AnnuityFactor/Widget/GetPeriods*
*src/Module/AnnuityFactor/Widget/get-periods.js.phtml* (JavaScript template)

Configuration assistant. Simplifies interaction with config fields to select
**payment method** and associated **duration**. For example, "Invoice", "12"
(as in 12 months). These values are required for various widgets to function.

If your application includes an administration panel you may want to add fields
for these values so that the user can change them as they please. This widget
helps you by supplying the data for these fields, and automatically adjust them.
This widget expects both fields to be select-boxes, but you should be able to
modify it to fit your requirements in most cases.

We will present a short example, where we generate and output everything within
the same **.phtml** file.

**Note: You are expected to pre-populate these fields yourself. This is because
we cannot make assumptions regarding how your hypothetical configuration system
works. You are expected to give give fields initial selection values. Our example
below will show an example of pre-population.**

**Note: that *period* means the number of *months* defined by an annuity factor.**

```php
<?php

// index.phtml

use \Resursbank\Ecom\Module\AnnuityFactor\Widget\GetPeriods;
use \Resursbank\Ecom\Module\AnnuityFactor\Repository as AnnuityFactorRepository;
use \Resursbank\Ecom\Module\PaymentMethod\Repository as PaymentMethodRepository;

$widget = new GetPeriods(
    storeId: 'store-id',
    methodElementId: 'payment-method-select-box',
    periodElementId: 'period-select-box',
    automatic: false // true by default, we set it to false for this example.
);

$paymentMethods = AnnuityFactorRepository::getMethods(
    storeId: 'store-id',
    PaymentMethodRepository::getPaymentMethods(
       storeId: 'store-id',
   )
);
$configuredPaymentMethod = 'e4eaaaf2-d142-11e1-b3e4-080027620cdd';
$annuityFactors = AnnuityFactorRepository::getAnnuityFactors(
    storeId: 'store-id',
    paymentMethodId: $configuredPaymentMethod,
);

?>

<form>
    <label for="payment-method-select-box">Payment method</label>
    <select id="payment-method-select-box">
        <?php foreach ($paymentMethods as $paymentMethod): ?>
            <option value="<?= $paymentMethod->id ?>">
                <?= $paymentMethod->name ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="period-select-box">Period</label>
    <select id="period-select-box">
        <?php foreach ($annuityFactors as $annuityFactor): ?>
            <option value="<?= $annuityFactor->durationMonths ?>">
                <?= $annuityFactor->paymentPlanName ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<script>
    <?= $widget->content ?>
   
    // If you had set automatic to true, this method would be called automatically.
    // upon document load. Since we set it to false, we need to call it manually.
    Resursbank_GetPeriods.generate({
        errorHandler: function (error) {
            console.error(error);
            alert(error);
         },
   });
</script>
```

We generate an instance of the PHP widget and supply it with a path to the
**payment method** and **period** select-boxes which we know will be present in
our form.

The widget will render the **get-periods.js.phtml** template, in context to
itself (this means that the PHP code within the template has access to everything
in the widget instance which is **public**) and store the rendered JavaScript
code in the **content** property which we later echo out in the script tag
(`<?= $widget->content ?>`).

We also set **automatic** to false, which means that the widget will
not automatically setup itself when the document is loaded, requiring us to call
the **generate** method manually instead.

```php
// Segment from Module/AnnuityFactor/Widget/get-periods.js.phtml where we
// automatically set up the widget if $automatic is set to true.
<?php if ($this->automatic): ?>
<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {
        Resursbank_GetPeriods.generate();
    }
);
</script>
<?php endif; ?>
```

This is useful if you want to control various aspects of the widgets behavior.

For example, in some systems form fields are loaded by AJAX queries after the
document has finished loading. In such circumstances, we wouldn't want the widget
to setup itself before the form fields are present.

Other reasons to set **automatic** to false could be that you do not like the
default behavior from parts of the widget. Maybe you want to add a custom error
handler, or you want to add a custom success handler. In such cases, you can
manually call the **generate** method on the widget instance and supply it with
your custom handlers.

The object you supply to the **generate** method is a list of functions, which
will override the default functions in the widget. Ergo, you can modify whatever
functionality you want while leaving the rest as is.

It should also be noted that **generate** is a helper method to create an instance
of the JavaScript class **Resursbank_GetPeriods**, setup relevant event listners
and populate form data all in one.

If you wanted even greater control, you could call **new Resursbank_GetPeriods()**
instead directly, like so:

```js
new Resursbank_GetPeriods({
     errorHandler: function (error) {
         console.error(error);
         alert(error);
      },
});
```

Note that doing this means you will need to setup event listeners etc. yourself.

We will now take a closer look at the widget JavaScript class and explain briefly
how it works.

**Resursbank_GetPeriods.data** will contain a JSON object with all available
payment methods, and their associated period values. This data is obtained from
**\Resursbank\Ecom\Module\AnnuityFactor\Widget\GetPeriods::getJsonData()**


**Resursbank_GetPeriods.generate** will create an instance of the
**Resursbank_GetPeriods** class, and forward the list of overriding functions.
It will also set up an event listeners for the **change** event on the
**payment method** select-box, calling **Resursbank_GetPeriods.updatePeriods**
when the event is triggered.

**Resursbank_GetPeriods.updatePeriods** will update the **period** select-box
with the correct values based on the selected **payment method**.

---

# [Module] Callback

*src/Module/Callback*

A helper module for handling callbacks from Resurs Bank. Callbacks are requests
sent from Resurs Bank to your server to notify you of changes to a payment, such
as it succeeding, failing, or being suspected of fraud.

There are two kinds of callbacks:

1. **Authorization** - Sent when a payment is authorized (customer performs purchase).
2. **Management** - Sent when a payment is *captured*, *refunded*, *cancelled* etc. (such events can relate to the entire, or parts of, the payment).

Ecom cannot set up routing for you, since this is individual to each
application. However, for the most common use case, we have included basic
classes for handling incoming requests and responding to them, to make your
integration easier. We will cover this with an example after we have described
the module a bit more.

## Repository

*src/Module/Callback/Repository*

Please note that the methods **\Resursbank\Ecom\Module\Callback\Repository::trackError()**,
**\Resursbank\Ecom\Module\Callback\Repository::trackInit()** and **\Resursbank\Ecom\Module\Callback\Repository::addDebugLogs()**
are public for testability. You are not expected to use these methods directly
from your application, as such we will not cover them in this documentation.

These functions are used internally by the
**\Resursbank\Ecom\Module\Callback\Repository::process()** method to track and
log events related to callbacks.

### -#- \Resursbank\Ecom\Module\Callback\Repository::triggerTest()

Trigger a test callback by submitting a URL to Resurs Bank. Resurs Bank will
then send a test callback to the URL, to ensure your website is reachable by

```php
use \Resursbank\Ecom\Module\Callback\Repository;

Repository::triggerTest(
    url: 'https://your-callback-url',
);
```

### -#- \Resursbank\Ecom\Module\Callback\Repository::process()

This is a shell method to process incoming callbacks. It's optional to use it,
but it can be helpful as it's designed to track and log related events.

We will explain how this works with an example in a little bit.

## Http

*src/Module/Callback/Http/*

Callback related controllers.

### -#- \Resursbank\Ecom\Module\Callback\Http\AuthorizationController

Contains a method to resolve request data from an incoming request as an
instance of **\Resursbank\Ecom\Lib\Model\Callback\Authorization**.

### -#- \Resursbank\Ecom\Module\Callback\Http\ManagementController

Contains a method to resolve request data from an incoming request as an
instance of **\Resursbank\Ecom\Lib\Model\Callback\Management**.

## [Widget] Callback

* *src/Module/Callback/Widget/Callback* (widget PHP class)
* *src/Module/Callback/Widget/callback.phtml* (HTML template)
* *src/Module/Callback/Widget/callback.css* (stylesheet)

This widget simply displays the expected URLs for **Authorization** and
**Management** callbacks. This is useful if you want to display the URLs in an
administration panel or similar.

```php
<?php

// index.phtml

use \Resursbank\Ecom\Module\Callback\Widget\Callbacks;

$widget = new Callbacks(
    authorizationUrl: 'https://your-authorization-callback-url',
    managementUrl: 'https://your-management-callback-url',
);

?>

<style>
    <?= $widget->css ?>
</style>

<?= $widget->content ?>
```

## Integration example

This example will illustrate a basic setup to accept incoming callbacks from
Resurs Bank and respond to them.

When creating a payment you will report the expected **authorization** callback
URL to Resurs Bank. When the payment is authorized, Resurs Bank will send a
request to this URL. The same goes for **management** callbacks.

For the purpose of this example, we will assume that you've reported this URL
to Resurs Bank: `https://my-domin/authorization-callback`

We will now imagine that your application will route this URL to the following
controller class:

```php
use Resursbank\Ecom\Lib\Model\Callback\Authorization as AuthorizationDataModel;
use \Resursbank\Ecom\Module\Callback\Http\AuthorizationController;
use \Resursbank\Ecom\Module\Callback\Repository as CallbackRepository;
use \Resursbank\Ecom\Lib\Model\Callback\Enum\Status;
use \Resursbank\Ecom\Lib\Model\Callback\CallbackInterface;

class MyAuthorizationController extends AuthorizationController
{
    // We assume your application routes the request to this method.
    public function exec()
    {
        CallbackRepository::process(
            // Resolve request data from request as a type-safe object.
            callback: $this->getRequestData(),
            // Local code to process callback, this will be executed as part of
            // the process method in the repository (first line of the first try
            // block, ~103).
            process: fn (CallbackInterface $callback) => $this->process($callback), 
         );
         
         // Respond with a 200 OK to inform Resurs Bank that the callback was
         // received and processed.
    }
    
    // Local code to process the callback.
    public function process(AuthorizationDataModel $callback)
    {
        // Resolve order from payment ID.
        $order = MyOrderREpository::getOrderFromPaymentId(
            paymentId: $callback->paymentId,
        );
        
        // Update order status or whatever in your system by comparing
        // $data->status, which is the status of the Payment at Resurs Bank.
        switch ($callback->status) {
            case Status::AUTHORIZED:
                $order->setStatus('authorized-but-not-paid');
                break;
            case Status::CAPTURED:
                $order->setStatus('paid');
                break;
            case Status::FROZEN:
                $order->setStatus('suspected-fraud');
                break;
            case Status::REJECTED:
                $order->setStatus('payment-not-accepted');
                break;
        }
    }
}
```

To summarize the above example:

1. Customer performs a purchase, in the request **you** submit to Resurs Bank, **you** define the URL for the upcoming **authorization** callback.
2. The callback is sent to your server, which routes it to the **MyAuthorizationController::exec** method.
3. The **exec** method will resolve the request data, and pass this to the Callback Repository for processing, along with a local method to handle the callback.
4. The Callback Repository will process the callback, and call the local method you supplied with the callback data.
5. Finally, the Callback Repository will log that the callback was processed.

Please note tha the Callback Repository will also handle logging of potential errors.

---

# [Module] Customer

*src/Module/Customer*

Customer related functionality.

## Repository

*src/Module/Customer/Repository*

Please note that the **\Resursbank\Ecom\Module\Customer\Repository::setSsnData()**
and **\Resursbank\Ecom\Module\Customer\Repository::getSsnData()** methods are
public for testability. You are not expected to use these methods directly from
your application, as such we will not cover them in this documentation.

When an address it fetched, we will attempt to store the supplied government ID
(SSN) within the session. This is so we can easily supply it during payment
creation, and thus avoiding the need for the client to repeat it at the gateway.

### -#- \Resursbank\Ecom\Module\Customer\Repository::getAddress()

Retrieve customer address from API.

```php
use \Resursbank\Ecom\Module\Customer\Repository;
use \Resursbank\Ecom\Lib\Order\CustomerType;

$address = Repository::getAddress(
    storeId: 'store-id',
    governmentId: 'customer-ssn-or-org-nr', // Like "198305147715" or "166997368573"
    customerType: CustomerType::NATURAL // Or CustomerType::LEGAL for companies.
);
```

## Http

*src/Module/Customer/Http*

HTTP controllers for customer related functionality.

### -#- \Resursbank\Ecom\Module\Customer\Http\AddressController

Controller for fetching customer address. 

```php
use \Resursbank\Ecom\Module\Customer\Http\GetAddressController;

class MyGetAddressController extends GetAddressController
{
    // We assume your application routes the request to this method. See the
    // GetAddress widget documentation below for an example of how this would be
    // incorporated in practice. For now, assume an AJAX request will be made to
    // this controller, and that the objective of the request is to collect
    // address information for a customer. As such, er assume your application
    // routes the URL "https://whatever.com/get-address" to this method.
    public function exec()
    {
        // Unless you have any special needs, simply call the parent method
        // which will resolve the incoming request data, fetch the address from
        // the API, and respond with the address data formatted as JSON. 
        parent::exec();
    }
}
```

## [Widget] GetAddress

* *src/Module/Customer/Widget/GetAddress* (widget PHP class)
* *src/Module/Customer/Widget/get-address.js.phtml* (JavaScript template)
* *src/Module/Customer/Widget/get-address.css* (stylesheet)
* *src/Module/Customer/Widget/get-address.phtml* (HTML template)

This will render a form where the customer can select their type ("NATURAL" or 
"LEGAL") and enter their government ID (SSN or organization number). The
JavaScript will is meant to submit this data to the **GetAddressController**,
which in turn will fetch the address from the API and return it as JSON.

Below is a complete example, assuming usage of the **MyGetAddressController**
from the previous example.

```php
<?php

// index.phtml

use \Resursbank\Ecom\Module\Customer\Widget\GetAddress;

// Note that you also can supply a $governmentId and $customerType to
// pre-populate the form should you wish to. Also, you can set $automatic to
// true if you do not want to modify the JavaScript code (get-address.js.phtml).
// For example, you may wish to display errors in a certain way, or display a
// customer loader while the request is being processed. For the purpose of this
// example, we will leave it as false, just to give you an idea of how you could
// modify the widget to fit your needs.
$widget = new GetAddress(
    url: 'https://whatever.com/get-addres'
);

?>

<style>
    <?= $widget->css ?>
</style>

<?= $widget->content ?>

<script>
    <?= $widget->js ?>

    // If you had set automatic to true, this method would be called automatically.
    // upon document load. Since we set it to false, we need to call it manually.
    let instance = new Resursbank_GetAddress({
        errorHandler: function (error) {
            console.error(error);
            alert(error);
         },
       updateAddress: function (address) {
           // Update the address fields.
       }
   });

    instance.setupEventListeners();
</script>
```

A brief explanation of the JavaScript class **Resursbank_GetAddress**:

**Resursbank_GetAddress.setupEventListeners()** will set up an event listener to submit the form when
the "Fetch Address" button is clicked.

This will execute **Resursbank_GetAddress.fetchAddress** which will first
validate the input data against the selected customer type, and then perform an
AJAX request to the URL supplied to the widget. The URL should route to the
**MyGetAddressController::exec** method.

This will return the address data as JSON, which will be passed to the
**updateAddress** function. This function must be passed to the widget as an
override, since the Ecom library cannot know how to populate your form with the
address data. There is just no one to know in advance how the fields are named
or typed.

---

# [Module] Payment

*src/Module/Payment*

Payment related functionality.

## Repository

*src/Module/Payment/Repository*

**Note that, while public, you should ignore 
*\Resursbank\Ecom\Module\Payment\Repository::getIntegrationInfoMetadata()*, this
it only utilised by modules developed for various platforms (such as *WordPress*
or *Magento*).**

### -#- \Resursbank\Ecom\Module\Payment\Repository::search()

Let's you search for legacy payments placed with older API:s. Useful if you are 
migrating from an older system to Ecom. If so, you can view this as your **get**
for old payments. These can then be handled just like any other payment.

**Note that this method will return a list of payments.**

```php
use \Resursbank\Ecom\Module\Payment\Repository;

// You can also search by government ID ($governmentId).
$payments = Repository::search(
    storeId: 'store-id',
    orderReference: 'legacy-payment-id',
);
```

### -#- \Resursbank\Ecom\Module\Payment\Repository::get()

Resolve payment data from API.

```php
use \Resursbank\Ecom\Module\Payment\Repository;

$payment = Repository::get(
    paymentId: 'payment-id',
);
```

### -#- \Resursbank\Ecom\Module\Payment\Repository::create()

Create a *payment session* at Resurs Bank. The natural behavior expected from
your checkout is that your client fills out their information, selects a payment
method, and then submits the form.

The client will then be redirected to Resurs Bank (the payment gateway) to
complete the payment. Before we can redirect the client, we must create a 
*payment session* at Resurs Bank so the bank has all the information necessary
to process the payment.

```php
use \Resursbank\Ecom\Module\Payment\Repository;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine;
use \Resursbank\Ecom\Lib\Model\Payment\Customer;
use Resursbank\Ecom\Lib\Model\Address;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Lib\Model\Payment\Customer;
use Resursbank\Ecom\Lib\Model\Payment\Customer\DeviceInfo;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Lib\Model\Payment\Options;
use Resursbank\Ecom\Lib\Model\Payment\RedirectionUrls;
use Resursbank\Ecom\Lib\Model\Payment\ParticipantRedirectionUrls;
use Resursbank\Ecom\Lib\Model\Payment\Callbacks;
use Resursbank\Ecom\Lib\Model\Payment\Callback;

$payment = Repository::create(
   storeId: 'store-id',
   paymentMethodId: 'payment-method-id',
   orderLines: new OrderLineCollection(data: [
      new OrderLine(
         quantity: 1,
         quantityUnit: 'pcs',
         vatRate: 25,
         unitAmountIncludingVat: 100,
         description: 'Article 1'
      ),
      new OrderLine(
         quantity: 1,
         quantityUnit: 'pcs',
         vatRate: 25,
         unitAmountIncludingVat: 100,
         description: 'Article 2'
      ),
    ]),
    // [Optional] Reference to the order in your system.
    orderReference: '10001010101',  
    // [Optional] To avoid client needing to re-enter info at gateway.
    customer: new Customer(
       deliveryAddress: new Address(
           street: '123 Fake Street',
           postalCode: '12345',
           city: 'Faketown',
           country: 'SE'
        ),
       customerType: CustomerType::NATURAL,
       email: 'john.doe@example.com',
       governmentId: '198305147715',
       mobilePhone: '0701234567'
   ),
   // [Optional] Options or payment and redirection back to your website.
   options: new Options(
       initiatedOnCustomersDevice: true,
       handleManualInspection: false,
       handleFrozenPayments: true,
       automaticCapture: false,
       redirectionUrls: new RedirectionUrls(
           customer: new ParticipantRedirectionUrls(
               // Landing page if payment fails.
               failUrl: 'https://example.com/fail', 
               // Landing page if payment succeeds.
               successUrl: 'https://example.com/success'
           ),
           coApplicant: null,
           merchant: null
       ),
       callbacks: new Callbacks(
           authorization: new Callback(
               // Notification sent to your system when payment is accepted /
               // rejected, so you can update the order in your system
               // accordingly.
               url: 'https://example.com/callback/authorization'
           ),
           management: null
       )
   )
);
```

There are two other **optional** parameters we've not included in the example
above:

1. $application - Information which is mostly relevant for loans.
2. $metadata - Additional information you want to store with the payment.

The example above will create a *payment session* for you. The URL you would
redirect the client to is:

```php
$payment->taskRedirectionUrls->customerUrl
```

The client will then complete the purchase at the gateway, after which two
things will happen:

1. The **authorization** callback will be sent to the URL you supplied in the
   **authorization** callback configuration (part of the **Options** object).
2. The client will be redirected back to your website, either to the **success**
   or **fail** URL you supplied in the **redirectionUrls** configuration (part of the **RedirectionUrls** object).

It's important to note that actions you apply on your order, or within other
parts of your system, should be performed as part of the **authorization**
callback. This is because the client can close the browser window after the
payment is completed, and never return to your website. The **authorization**
callback is the only way to ensure that your system is updated with the correct
information.

### -#- \Resursbank\Ecom\Module\Payment\Repository::capture()

Capture part / all of a payment.

Please read the chapter on **Actions applying to part of a payment** first.

```php
use \Resursbank\Ecom\Module\Payment\Repository;

$payment = Repository::capture(
    paymentId: 'payment-id'
);
```

The above example will capture the full amount of the payment. You can also
supply the following optional arguments when capturing:

 - **orderLines** - A collection of specific **OrderLine** objects to capture.
 - **creator** - Reference to the person who performed the capture.
 - **transactionId** - *Your* reference to the event. This will appear in the
   *accounting file* to help you track the event.
 - **invoiceId** - Reference to potential *invoice* in your local system.

### -#- \Resursbank\Ecom\Module\Payment\Repository::refund()

Refund part / all of a payment.

Please read the chapter on **Actions applying to part of a payment** first.

```php
use \Resursbank\Ecom\Module\Payment\Repository;

$payment = Repository::refund(
    paymentId: 'payment-id'
);
```

The above example will refund the full amount of the payment. You can also
supply the following optional arguments when refunding:

 - **orderLines** - A collection of specific **OrderLine** objects to refund.
 - **creator** - Reference to the person who performed the refund.
 - **transactionId** - *Your* reference to the event. This will appear in the
   *accounting file* to help you track the event.
 - **refundNoteId** - Reference to potential *credit note* (credit invoice / refund note) in your local system.

### -#- \Resursbank\Ecom\Module\Payment\Repository::cancel()

Cancel part / all of a payment.

Please read the chapter on **Actions applying to part of a payment** first.

```php
use \Resursbank\Ecom\Module\Payment\Repository;

$payment = Repository::cancel(
    paymentId: 'payment-id'
);
```

The above example will cancel the full amount of the payment. You can also
supply the following optional arguments when cancelling:

 - **orderLines** - A collection of specific **OrderLine** objects to cancel.
 - **creator** - Reference to the person who performed the cancellation.

### -#- \Resursbank\Ecom\Module\Payment\Repository::setMetadata()

Append additional metadata to an existing payment.

```php
use \Resursbank\Ecom\Module\Payment\Repository;
use \Resursbank\Ecom\Lib\Model\Payment\Metadata;
use \Resursbank\Ecom\Lib\Model\Payment\Metadata\EntryCollection;
use \Resursbank\Ecom\Lib\Model\Payment\Metadata\Entry;

$payment = Repository::setMetadata(
    paymentId: 'payment-id',
    metadata: new Metadata(
       creator: 'Seombody',
       custom: new EntryCollection(data: [
           new Entry(
               key: 'key',
               value: 'value'
           ),
           new Entry(
               key: 'key2',
               value: 'value2'
           ),
       ]),
    )
);
```

### -#- \Resursbank\Ecom\Module\Payment\Repository::addOrderLines()

Add order lines to an existing payment.

```php
use \Resursbank\Ecom\Module\Payment\Repository;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine;

$payment = Repository::addOrderLines(
    paymentId: 'payment-id',
    orderLines: new OrderLineCollection(data: [
       new OrderLine(
           quantity: 1,
           quantityUnit: 'pcs',
           vatRate: 25,
           unitAmountIncludingVat: 100,
           description: 'Article 1'
       ),
       new OrderLine(
           quantity: 1,
           quantityUnit: 'pcs',
           vatRate: 25,
           unitAmountIncludingVat: 100,
           description: 'Article 2'
       ),
    ]),
);
```

### -#- \Resursbank\Ecom\Module\Payment\Repository::updateOrderLines()

Replace the order lines of an existing payment. This method will:

1. Execute **cancel()** to cancel the existing payment (this basically just cancels all items attached to the payment, it does not cancel the payment object itself).
2. Execute **addOrderLines()** to add the new order lines to the payment.

This method will also perform some special validation checks before performing
these operations. To assert that the payment is in a state where it can be
updated, and that the new order lines are valid for it. For example, you cannot
replace order lines on a payment that has already been captured, and you cannot
add order lines which would exceed to total authorized amount of the payment.

```php
use \Resursbank\Ecom\Module\Payment\Repository;

$payment = Repository::updateOrderLines(
    paymentId: 'payment-id',
    orderLines: new OrderLineCollection(data: [
       new OrderLine(
           quantity: 1,
           quantityUnit: 'pcs',
           vatRate: 25,
           unitAmountIncludingVat: 100,
           description: 'Article 1'
       ),
       new OrderLine(
           quantity: 1,
           quantityUnit: 'pcs',
           vatRate: 25,
           unitAmountIncludingVat: 100,
           description: 'Article 2'
       ),
    ]),
);
```

### -#- \Resursbank\Ecom\Module\Payment\Repository::getTaskStatusDetails()

This returns an object containing actions the merchant or customer needs to
perform in relation to a payment. It may also contain URL:s they need to visit
in order to perform said actions.

```php
use \Resursbank\Ecom\Module\Payment\Repository;

$payment = Repository::getTaskStatusDetails(
    paymentId: 'payment-id',
);
```

## [Widget] PaymentInformation

* *src/Module/Payment/Widget/PaymentInformation* (widget PHP class)
* *src/Module/Payment/Widget/payment-information.phtml* (HTML template)
* *src/Module/Payment/Widget/payment-information.css* (stylesheet)

Renders a window displaying information about a payment. This information is
mostly useful to administrators (merchants) to give them a quick overview of
the payment attached to an order, without having to access the Merchant Portal.

```php
<?php

// index.phtml

use Resursbank\Ecom\Module\Payment\Widget\PaymentInformation;
use Resursbank\Ecom\Module\PaymentMethod\Enum\CurrencyFormat;

// Spoof a UUID for paymentId
$paymentId = '123e4567-e89b-12d3-a456-426614174000';

// Create an instance of the PaymentInformation widget
$widget = new PaymentInformation(
    paymentId: $paymentId,
    currencySymbol: 'kr',
    currencyFormat: CurrencyFormat::SYMBOL_LAST
);

?>

<?= $widget->content ?>

<style>
    <?= $widget->css ?>
</style>
```

The widget will also provide the following properties:

* *$payment* - Instance of the \Resursbank\Ecom\Lib\Model\Payment object rendered.
* *$logo* - Resurs Bank logotype SVG.

---

# [Module] PaymentHistory

*src/Module/PaymentHistory*

Tracks and reflects payment events.

## DataHandler

*src/Module/PaymentHistory/DataHandler*

The **DataHandler** is a helper class to manage the payment history data. It
will read and write history entries to persistent storage. We provide two
integration options out of the box:

* *FileDataHandler* - Writes history entries to a file on disk.
* *VoidDataHandler* - Does nothing. Useful for testing.

If you have a better mean of storing the data, you can create your own data
handler by implementing the **DataHandlerInterface**.

For example, if you have a database you may prefer to keep the history entries
there. You would then create a **DatabaseDataHandler** class that implements
**DataHandlerInterface**, and pass an instance of this class to the
**\Resursbank\Ecom\Config::setup()** (see *$paymentHistoryDataHandler* arg).

**Note that events tracked are only actions performed through the Ecom library,
like when capturing / refunding a payment using the *Payment* Module, as well
as incoming requests from Resurs Bank, like *callbacks*. Everything that happens
to a payment on the API side, we do not track.**

**Note that *previousOrderStatus* and *currentOrderStatus* are optional
values for the *Entry* object to keep track of changes applied on the order
status when various events are executed.**

## Translator

*src/Module/PaymentHistory/Translator*
*src/Module/PaymentHistory/translations.json*

This is a custom translator for the payment history module. Translations are
located inside the *translations.json* file.

## Repository

*src/Module/PaymentHistory/Repository*

### -#- \Resursbank\Ecom\Module\PaymentHistory\Repository::write()

Write a history entry to persistent storage (like a file on disk or db).

```php
use \Resursbank\Ecom\Module\PaymentHistory\Repository;
use \Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Event;
use Resursbank\Ecom\Lib\Model\PaymentHistory\User;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Result;

$entry = new Entry(
    paymentId: '123e4567-e89b-12d3-a456-426614174000',
    event: Event::CANCELED,
    user: User::CUSTOMER,
    result: Result::INFO,
    extra: "Goodbye payment!",
    previousOrderStatus: 'pending',
    currentOrderStatus: 'cancelled',
    reference: 'my-order',
    userReference: 'USERREF123456'
);

Repository::write(entry: $entry);
```

### -#- \Resursbank\Ecom\Module\PaymentHistory\Repository::getList()

Resolve list of events for a payment. This method takes two arguments:

1. **$paymentId** - The payment ID to fetch history entries for.
2. **$event** - Optional. Filter the history entries by event type (*\Resursbank\Ecom\Lib\Model\PaymentHistory\Event*).

```php
use \Resursbank\Ecom\Module\PaymentHistory\Repository;
use \Resursbank\Ecom\Lib\Model\PaymentHistory\Event;

// Fetch all entries for a payment.
$entries = Repository::getList(
    paymentId: '123e4567-e89b-12d3-a456-426614174000'
);

// Fetch events of a specific type.
$entries = Repository::getList(
    paymentId: '123e4567-e89b-12d3-a456-426614174000',
    event: Event::CAPTURED
);
```

### -#- \Resursbank\Ecom\Module\PaymentHistory\Repository::hasExecuted()

Check if an event has been executed for a payment.

```php
use \Resursbank\Ecom\Module\PaymentHistory\Repository;
use \Resursbank\Ecom\Lib\Model\PaymentHistory\Event;

$hasExecuted = Repository::hasExecuted(
    paymentId: '123e4567-e89b-12d3-a456-426614174000',
    event: Event::CANCELED
);
```

### -#- \Resursbank\Ecom\Module\PaymentHistory\Repository::getError()

Format *\Throwable* so it will render nicely in the history widget (documented
below).

```php
use \Resursbank\Ecom\Module\PaymentHistory\Repository;
use \Exception;

$error = Repository::getError(
    new Exception('Something went wrong.')
);
```

## [Widget] Log

* *src/Module/PaymentHistory/Widget/Log* (widget PHP class)
* *src/Module/PaymentHistory/Widget/log.phtml* (HTML template)
* *src/Module/PaymentHistory/Widget/log.css* (stylesheet)
* *src/Module/PaymentHistory/Widget/log.js* (JavaScript)

This widget will render a log of payment events. It's useful for administrators
to get an idea of how a payment has been handled.

```php
<?php

// index.phtml

use \Resursbank\Ecom\Module\PaymentHistory\Widget\Log;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Event;
use Resursbank\Ecom\Lib\Model\PaymentHistory\User;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Result;

$entries = new EntryCollection([
    new Entry(
        paymentId: '123e4567-e89b-12d3-a456-426614174002',
        event: Event::REDIRECTED_TO_GATEWAY,
        user: User::CUSTOMER,
        result: Result::INFO,
        extra: "Some epic data.",
        previousOrderStatus: 'new',
        currentOrderStatus: 'new',
        reference: 'my-order',
        userReference: 'USERREF123456'
    ),
    new Entry(
        paymentId: '123e4567-e89b-12d3-a456-426614174002',
        event: Event::REACHED_ORDER_SUCCESS_PAGE,
        user: User::CUSTOMER,
        result: Result::SUCCESS,
        previousOrderStatus: 'new',
        currentOrderStatus: 'waiting-for-payment',
        reference: 'my-order',
        userReference: 'USERREF123457'
    )
]);

// In reality, you would use Repository::getList() to fetch the entries. This
// is just an example to illustrate how the widget works.
$widget = new Log(entries: $entries);

?>


<?= $widget->content ?>


<style>
  <?= $widget->css ?>
</style>

<script>
  <?= $widget->js ?>
</script>
```

Please note that the **Extra** column will display the data contained in
**entry.extra** directly if it's length is less or equal to 40 characters. If
it's longer, it will display a button which will replace the content inside the
modal window with the full content.

---

# [Module] PaymentMethod

*src/Module/PaymentMethod*

Payment method related functionality.

## Repository

*src/Module/PaymentMethod/Repository*

**Note that the following methods are public for testability and are not meant
to be used directly from your application.**

- getCache() // Resolve list payment methods from cache.
- getApi() // Resolve list of payment methods from API.

### -#- \Resursbank\Ecom\Module\PaymentMethod\Repository::getPaymentMethods()

Resolve a list of available payment methods.

```php
use \Resursbank\Ecom\Module\PaymentMethod\Repository;

$paymentMethods = Repository::getPaymentMethods(
    storeId: 'store-id'
);
```

You can also supply an optional *$amount* parameter filter payment methods.

## [Widget] PartPayment

* *src/Module/PaymentMethod/Widget/PartPayment* (widget PHP class)
* *src/Module/PaymentMethod/Widget/part-payment.phtml* (HTML template)
* *src/Module/PaymentMethod/Widget/part-payment.css* (stylesheet)
* *src/Module/PaymentMethod/Widget/part-payment.js.phtml* (JavaScript template)

This renders a widget with part payment data, meant to be rendered in association
with a product (like a product page) or payment method (like checkout). This data
consists primarily of a suggested starting cost based on information passed to
the widget instance (*paymentMethod*, *months* and *amount*).

It also expects to handle a **ReadMore** widget instance (this widget renders a
link and associated modal displaying information relating to a payment method).
The ReadMore widget is optional and can be omitted if you like, but for a more
complete example we will illustrate below how it should be rendered in
association with the PartPayment widget. The Read More widget itself will be
covered by its own chapter later in this document.

```php
<?php

// index.phtml

use Resursbank\Ecom\Module\PaymentMethod\Widget\PartPayment;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Module\PaymentMethod\Enum\CurrencyFormat;
use Resursbank\Ecom\Module\PaymentMethod\Widget\ReadMore;
use Resursbank\Ecom\Module\PaymentMethod\Repository;

$paymentMethod = Repository::getPaymentMethods(
    storeId: 'store-id'
)->current();

// Create an instance of the PartPayment widget.
$widget = new PartPayment(
    storeId: 'store-id',
    paymentMethod: $paymentMethod,
    months: 12,
    amount: 1000.0,
    currencySymbol: 'kr',
    currencyFormat: CurrencyFormat::AFTER,
    fetchStartingCostUrl: 'https://example.com/fetch-starting-cost' // This corresponds to a custom endpoint in your application where an AJAX request can fetch an updated version of the widget upon need.
);

$readMoreWidget = new ReadMore(
    paymentMethod: $paymentMethod,
    amount: 1000.0,
);

?>

<style>
   <?= $widget->css ?>
   <?= $readMoreWidget->css ?>
</style>

<div id="rb-pp-widget-container"> <!-- NOTE! This will be used in our JS component to reference the HTML of the widget. -->
    <?= $widget->content ?>
   <?= $$readMoreWidget->content ?>
</div>

<script>
   <?= $widget->js ?>
</script>
```

The JavaScript component for this widget cannot be automatically initialized as
it requires application specific information to function.

It will automatically fetch, and update, the suggested starting cost as inputs
regulating the price of an object (product / shopping cart) change.

For example, you may have a product where the customer can select a color, and
each color may append a different price to the product. Another very common
input for both products and cart systems are quantity inputs.

This JavaScript components lets you specify which elements to observe, and how
to calculate the amount based on these elements. It will then fetch new data for
the widget based on the new amount.

Below is an example of how this JavaScript component could be configured. The
example is based on code from one of our Magento modules.

```js
<script>
  const overrides = {
     /**
      * When we change the configuration in the add-to-cart form, or the
      * innerHTML of the price element changes, update the price information of
      * the widget.
      */
     getObservableElements: function() {
        const formElement = document.querySelector(
          "#product_addtocart_form"
        );
        const priceElement = document.querySelector(
          "#rb-pp-widget-container [data-role='priceBox']"
        );

        const result = [formElement, priceElement];

        if (result.length < 1) {
           throw new Error(
             console.warn('part-payment-widget-missing-observable-elements')
           );
        }

        return result;
     },

     /**
      * Resolve element we will get price data from.
      */
     getAmountElement: function() {
        const el = document.querySelector("#product_addtocart_form [data-role="priceBox"] .price");

        if (el === null) {
           throw new Error(
             console.warn('rb-part-payment-widget-missing-amount-element')
           );
        }

        return el;
     },

     /**
      * Resolve element we will get qty data from.
      */
     getQtyElement: function() {
        const el = document.querySelector('#product_addtocart_form [name="qty"]');

        if (el === null) {
           throw new Error(
             console.warn('rb-part-payment-widget-missing-qty-element')
           );
        }

        return el;
     },
        
     /**
      * Override content-type for AJAX request. Magento does not support
      * application/json out of the box in frontend controllers, and we
      * need to include the form_key in the request to support cross-site
      * request forgery protection.
      *
      * @returns {string}
      */
     getRequestContentType: function() {
        return 'application/x-www-form-urlencoded';
     },

     /**
      * We must include the form_key with POST requests.
      */
     getCustomRequestData: function() {
        return {
           form_key: $.mage.cookies.get('form_key')
        };
     },
        
   /**
      * Override body for AJAX request. Magento does not support
      * application/json out of the box in frontend controllers, and we
      * need to include the form_key in the request to support cross-site
      * request forgery protection.
      *
      * @returns {string}
      */
     getRequestBody: function(amount) {
        return new URLSearchParams(this.getRequestData(amount)).toString();
     }
  };

   // We need to pick up amount in a special way for grouped products.
   //
   // Note that RB_PP_PRODUCT_TYPE is a const speicifed in our Magento module
   // that tells us what type of product we are dealing with. This is not a
   // part of the Ecom library.
   if (RB_PP_PRODUCT_TYPE === 'grouped') {
     overrides.getAmount = function() {
        // Get all TR elements in #super-product-table.
        const trElements = document.querySelectorAll(
                '#super-product-table tbody tr'
        );

        let result = 0;

        trElements.forEach((tr) => {
           // Get the price element in the current TR.
           const priceElement = tr.querySelector("[data-role='priceBox'] .price");

           // If we cannot find a price element, skip this TR.
           if (priceElement === null) {
              return;
           }

           // Get price from innerHTML.
           const price = Resursbank_PartPayment.parseFromContentToFloat(
                   priceElement.innerHTML
           );

           // Get qty element in the current TR.
           const qtyElement = tr.querySelector("input.qty");

           // If we cannot find a qty element, use 1 as default.
           const qty = qtyElement === null ?
                   0 :
                   Resursbank_PartPayment.parseFromContentToFloat(qtyElement.value);

           result += price * qty;
        });

        return result;
     };
  }

  // Store instance in local variable for later use.
  RB_PP_WIDGET_INSTANCE = Resursbank_PartPayment.createInstance(
    document.getElementById('rb-pp-widget-container'),
    overrides
  );

   // Reload instance when the DOM of #product_addtocart_form changes,
   // Becacuse Magento will load elements for the product page on-the-fly
   // using AJAX requests, we cannot be sure when it's actaully loaded all 
   // input elements, so we need to reset our observers when the DOM changes.
   document
     .getElementById('product_addtocart_form')
     .addEventListener('DOMSubtreeModified', function() {
        RB_PP_WIDGET_INSTANCE.reloadElementObservers();
     });
</script>
```

The example above is incredibly complex and specific to a Magento module we've
written. However, it illustrates how flexible the widget is, and how you can
configure it to fit your needs by overriding the original methods in
**Resursbank_PartPayment**

We will now show you a much simpler example. Say that the only thing you wish to
observe for changes is an input box on a product page:

```js
<script>
   // Reload element observers on document load.
   docuemnt.addEventListener('DOMContentLoaded', function() {
      Resursbank_PartPayment.createInstance(
        document.getElementById('rb-pp-widget-container'), // Widget container element
        {
           getObservableElements: function() {
              return [document.getElementById('qty')];
           },
           getAmountElement: function() {
              return document.getElementById('price');
           },
           getQtyElement: function() {
             return document.getElementById('qty');
          }
        }
      );
    });
</script>
```

The default amount calculation performed by the widget goes as follows:

1. Parse a float from the value of the *amount* element.
2. Parse a float from the value of the *qty* element.
3. Multiply the two values.

So in a scenario where quantity is the only thing which can affect the price of
a product, the above example would be sufficient.

We will also briefly show an example of a controller where the AJAX request
performed by the JavaScript component can fetch new data for the widget.

```php
use Resursbank\Ecom\Module\PaymentMethod\Http\PartPayment\InfoControllerInterface;
use Resursbank\Ecom\Lib\Model\PaymentMethod\PartPayment\InfoResponse;
use Resursbank\Ecom\Module\PaymentMethod\Widget\PartPayment;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Module\PaymentMethod\Enum\CurrencyFormat;
use Resursbank\Ecom\Module\PaymentMethod\Widget\ReadMore;
use Resursbank\Ecom\Module\PaymentMethod\Repository;
use Throwable;

class MyPartPaymentInfoController implements InfoControllerInterface
{
     // Controller entry point.
    public function execute(): void
    {
        // Resolve amount from request body.
        $amount = $_POST['amount'] ? (float) $_POST['amount'] : 0.0;
        
        // Note that this is an inadvisable design pattern and only here for
        // illustrative purposes. 
        echo json_encode($this->getResponse($amount));
        exit;
    }

    /**
     * Resolve response data.
     *
     * @param float $amount
     * @return InfoResponse
     * @throws AttributeCombinationException
     * @throws ConfigException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function getResponse(float $amount): InfoResponse
    {
         $widget = null;
         $readMoreWidget = null;
    
         try {
            $paymentMethod = Repository::getPaymentMethods(
                storeId: 'store-id'
            )->current();
   
            // Create an instance of the PartPayment widget.
            $widget = new PartPayment(
               storeId: 'store-id',
               paymentMethod: $paymentMethod,
               months: 12,
               amount: $amount,
               currencySymbol: 'kr',
               currencyFormat: CurrencyFormat::AFTER,
               fetchStartingCostUrl: 'https://example.com/fetch-starting-cost' // This corresponds to a custom endpoint in your application where an AJAX request can fetch an updated version of the widget upon need.
            );
            
            $readMoreWidget = new ReadMore(
                paymentMethod: $paymentMethod,
                amount: $amount
            );
         } catch (Throwable $e) {
           // Error handling.
         }

        return new InfoResponse(
            startingAt: (float) $widget?->cost->monthlyCost,
            startingAtHtml: (string) $widget?->getStartingAt(),
            readMoreWidget: (string) $readMoreWidget?->content
        );
    }
}
```

You should submit the URL to this controller as *fetchStartingCostUrl* when
configuring the *PartPayment* widget so that AJAX requests to fetch updated data
for the widget can be made.

## [Widget] ReadMore

* *src/Module/PaymentMethod/Widget/ReadMore* (widget PHP class)
* *src/Module/PaymentMethod/Widget/read-more.phtml* (HTML template)
* *src/Module/PaymentMethod/Widget/read-more.css* (stylesheet)

This widget will render a link which, when clicked, will display a modal window
with information about a payment method.

The widget HTML contains some inline JavaScript to toggle modal visibility.

```php
<?php

// index.phtml

use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Module\PaymentMethod\Widget\ReadMore;
use Resursbank\Ecom\Module\PaymentMethod\Repository;

$paymentMethod = Repository::getPaymentMethods(
    storeId: 'store-id'
)->current();

$widget = new ReadMore(
   paymentMethod: $paymentMethod,
   amount: 150.25
);

?>

<style>
   <?= $widget->css ?>
</style>

<?= $widget->content ?>
```

## [Widget] PaymentMethods

* *src/Module/PaymentMethod/Widget/PaymentMethods* (widget PHP class)
* *src/Module/PaymentMethod/Widget/payment-methods.phtml* (HTML template)

This widget will render a list of available payment methods. It's useful to
check which payment methods *should* be available in checkout (**note that
methods can filtered from checkout based on various conditions such as cart
total.**).

While the content of this widget isn't sensitive it's meant to be rendered in
an administration like panel, available only to merchants. Like the admin panel
for WordPress or Magento.

```php
use Resursbank\Ecom\Module\PaymentMethod\Widget\PaymentMethods;
use Resursbank\Ecom\Module\PaymentMethod\Repository;

$widget = new PaymentMethods(
    paymentMethods: Repository::getPaymentMethods(
        storeId: 'store-id'
    )
);

<?= $widget->content ?>
```

## [Widget] UniqueSellingPoint

* *src/Module/PaymentMethod/Widget/UniqueSellingPoint* (widget PHP class)
* *src/Module/PaymentMethod/Widget/unique-selling-point.phtml* (HTML template)

Renders a USP message for a payment method. This is essentially information
about a payment method to help customers decide which method to chose. Like
"Pay with your mobile phone" for *Swish* in Sweden.

```php
<?php

// index.phtml

use Resursbank\Ecom\Module\PaymentMethod\Widget\UniqueSellingPoint;
use Resursbank\Ecom\Module\PaymentMethod\Widget\ReadMore;
use Resursbank\Ecom\Module\PaymentMethod\Repository;

$paymentMethod = Repository::getPaymentMethods(
    storeId: 'store-id',
    amount: 5000.00
)->current();

$widget = new UniqueSellingPoint(
    paymentMethod: $paymentMethod,
    amount: 5000.00
);

?>

<?= $widget->content ?>
```

---

# [Module] PriceSignage

*src/Module/PriceSignage*

Use the API to calculate part payment costs.

## Repository

*src/Module/PriceSignage/Repository*

**Note that the following methods, while public, are not meant to be used
directly from your application.**

- getCache() // Resolve data from cache.
- getApi() // Resolve data from API.

### -#- \Resursbank\Ecom\Module\PriceSignage\Repository::getPriceSignage()

Use the API to calculate expected monthly cost using a specific payment method.

```php
use \Resursbank\Ecom\Module\PriceSignage\Repository;
use Resursbank\Ecom\Module\PaymentMethod\Repository as PaymentMethodRepository;

$paymentMethod = PaymentMethodRepository::getPaymentMethods(
    storeId: 'store-id',
    amount: 5000.00
)->current();

$data = Repository::getPriceSignage(
    storeId: 'store-id',
    paymentMethodId: $paymentMethod->id,
    amount: 1000.0
);
```

---

# [Module] Store

*src/Module/Store*

Store related functionality. Store in this context refers to *stores* connected
with your API account at Resurs Bank.

## Repository

*src/Module/Store/Repository*

**Note that the following methods are public for testability and are not meant
to be used directly from your application.**

* getCache() // Resolve list of stores from cache.
* getApi() // Resolve list of stores from API.

### -#- \Resursbank\Ecom\Module\Store\Repository::getStores()

Resolve a complete list of stores associated with your API account.

```php
use \Resursbank\Ecom\Module\Store\Repository;

$stores = Repository::getStores();
```

## [Widget] GetStores

This is a widget which renders a JavaScript component to automatically fetch a
list of available stores based on API credentials applied in a form.

For example, this is useful when, in the context of an administration panel, the
merchant supplies their credentials, and you then wish to fetch a list of
available stores to let them select which store should be used in the context
of the supplied credentials.

You will need to create a custom endpoint within your application for the AJAX
requests to fetch the store data. We will give an example of this below as well.

**Note that what stores are available is also affected by which *environment* is
configured (TEST / PROD).**

If you do not have any special requirements, and you know the ID of all relevant
elements in advance, then the following example should suffice to get everything
working for you.

```php
<?php

// index.phtml

use Resursbank\Ecom\Module\Store\Widget\GetStores;

// Create an instance of the GetStores widget with spoofed IDs
$widget = new GetStores(
    url: 'https://example.com/fetch-stores',
    automatic: true,
    storeSelectId: 'store-select',
    environmentSelectId: 'environment-select',
    clientIdInputId: 'client-id-input',
    clientSecretInputId: 'client-secret-input',
);

?>

<?= $widget->content ?>
```

If you should have any special requirements you can leave *automatic* as false
to initiate the JavaScript component manually and override whatever functions
you require to modify.

For example, in some of our modules we have duplicate sets of inputs for API
credentials to let you quickly toggle between test / production accounts.
Because of this, we need to override *Resursbank_FetchStores.getClientIdElement*
and *Resursbank_FetchStores.getClientSecretElement* to resolve the correct ones
based on what the *environment* configuration option in our config context is
set to (TEST / PROD).

```php
<?php

// index.phtml

use Resursbank\Ecom\Module\Store\Widget\GetStores;

// Create an instance of the GetStores widget with spoofed IDs
$widget = new GetStores(
    url: 'https://example.com/fetch-stores',
    storeSelectId: 'store-select',
    environmentSelectId: 'environment-select',
);

?>

<?= $widget->content ?>

<script>
   // Image this can return either "test" or "prod" based on a select box.
   function getEnvironment() {
       return 'test';
   }
   
   const fetcher = new Resursbank_FetchStores({
        getClientIdElement: function() {
            return document.getElementById('client_id' + getEnvironment());
        },
   
        getClientSecretElement: function() {
           return document.getElementById('client_secret' + getEnvironment());
        },
    });
   
    fetcher.setupEventListeners();
</script>
```

An example of the controller you would need to create to fetch the store data
for the widget is shown below.

```php
use Resursbank\Ecom\Module\Store\Http\GetStoresController;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Api\Environment;
use Resursbank\Ecom\Lib\Api\GrantType;

class MyGetStoresController extends GetStoresController
{
    public function exec(): array
    {
        $result = [];

        // Resolve credentials from AJAX request.
        $request = $this->getRequestData();
        
        $scope = $request->environment === Environment::PROD ?
            Scope::MERCHANT_API :
            Scope::MOCK_MERCHANT_API;
            
            
        // Re-configure Ecom setup with credentials in AJAX request.
        Config::setup
            jwtAuth: new Jwt(
                clientId: $request->clientId,
                clientSecret: $request->clientSecret,
                scope: $scope,
                grantType: GrantType::CREDENTIALS
            ),
            scope: $scope
        );

        $stores = StoreRepository::getStores();
        
        /** @var Store $store */
        foreach ($stores as $store) {
            $result[$store->id] = $store->name;
        }

        // This is an inadvisable design pattern only used for illustration.
        echo json_encode($result);
        exit;
    }
}
```

**Note that the optional *spinnerClass* parameter can be supplied to the widget
configuration to append a CSS class on the store selection element whenever new
data is being fetched. you can also show a customised AJAX loader by overriding
the *Resursbank_FetchStores.onToggle* method.**

---

# [Module] SupportInfo

*src/Module/SupportInfo*

Gather information for our support staff in case of problems.

## [Widget] SupportInfo

* *src/Module/SupportInfo/Widget/SupportInfo* (widget PHP class)
* *src/Module/SupportInfo/Widget/support-info.phtml* (HTML template)
* *src/Module/SupportInfo/Widget/support-info.css* (stylesheet)

**Warning! This widget renders sensitive system information. It should only be
rendered in a secure context, such as inside an administration panel!**

This widget renders a block containing various system information which is
relevant to our support staff when debugging issues reported by customers. This
information includes the following (list is subject to change):

* PHP version
* OpenSSL version
* Curl version
* Ecom library version (from composer.json)

```php
// index.phtml

<?php
use Resursbank\Ecom\Module\SupportInfo\Widget\SupportInfo;

$widget = new SupportInfo();

?>

<?= $widget->html ?>

<style>
  <?= $widget->css ?>
</style>
```

**Note that the optional *pluginVersion* argument is only relevant to our local
development team to identify platform specific modules.**
