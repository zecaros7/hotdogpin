<?php
use Slim\Http\Request;
use Slim\Http\Response;
use Stripe\Stripe;
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::create(realpath('../../..'));
$dotenv->load();
Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

$db = new SQLite3('./store.db');
$db->exec("CREATE TABLE IF NOT EXISTS sessions(id INTEGER PRIMARY KEY, stripe_id TEXT, status TEXT)");
// createSession
function createSession($sessionId) {
  global $db;
  $stmt = $db->prepare("INSERT INTO sessions(stripe_id, status) VALUES (:id, 'pending')");
  $stmt->bindValue(':id', $sessionId, SQLITE3_TEXT);
  return $stmt->execute();
}
// markSessionPaid
function markSessionPaid($sessionId) {
  global $db;
  $stmt = $db->prepare("UPDATE sessions SET status='paid' WHERE :id = stripe_id");
  $stmt->bindValue(':id', $sessionId, SQLITE3_TEXT);
  return $stmt->execute();
}
// getSessionStatus
function getSessionStatus($sessionId) {
  global $db;
  $stmt = $db->prepare("SELECT status FROM sessions WHERE :id = stripe_id");
  $stmt->bindValue(':id', $sessionId, SQLITE3_TEXT);
  $result = $stmt->execute();
  return $result->fetchArray()[0];
}

$app = new Slim\App;

$app->get('/', function (Request $request, Response $response, $args) {
  $response->getBody()->write(file_get_contents("../../client/index.html"));
  return $response;
});
$app->get('/happyflow', function (Request $request, Response $response, $args) {
  $response->getBody()->write(file_get_contents("../../client/happyflow.html"));
  return $response;
});
$app->get('/cancel', function (Request $request, Response $response, $args) {
  $response->getBody()->write(file_get_contents("../../client/cancel.html"));
  return $response;
});

$app->post('/create-session', function(Request $request, Response $response) use ($app)  {
  $params = json_decode($request->getBody());
  try {
    // One time payments
    $session = \Stripe\Checkout\Session::create([
      'payment_method_types' => ['card', 'ideal'],
      'line_items' => [[
        'name' => 'Hotdog Pin',
        'description' => 'Interest Hotdog Pins for Sale',
        'images' => ['https://www.hotdogpins.com/imgs/previews/thinking_hotdog.png'],
        'amount' => 5,
        'currency' => 'sgd',
        'quantity' => 1,
      ]],
      'success_url' => 'http://localhost:4242/happyflow?session_id={CHECKOUT_SESSION_ID}',
      'cancel_url' => 'http://localhost:4242/cancel',
    ]);
    createSession($session->id);
  } catch (Exception $e) {
    return $response->withJson($e->getJsonBody(), 400);
  }
  return $response->withJson($session);
});

$app->post('/webhook', function(Request $request, Response $response) use ($app)  {

  // You can find your endpoint's secret in your webhook settings
  $endpoint_secret = getenv('STRIPE_WEBHOOK_SECRET');
  $payload = $request->getBody();
  $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
  $event = null;

  try {
    $event = \Stripe\Webhook::constructEvent(
      $payload, $sig_header, $endpoint_secret
    );
  } catch(\UnexpectedValueException $e) {
    // Invalid payload
    http_response_code(400);
    exit();
  } catch(\Stripe\Exception\SignatureVerificationException $e) {
    // Invalid signature
    http_response_code(400);
    exit();
  }

  // Handle the checkout.session.completed event
  if ($event->type == 'checkout.session.completed') {
    $session = $event->data->object;

    // Fulfill the purchase...
    handle_checkout_session($session);
  }

  return $response->withJson(['message' => 'success']);
});

$app->get('/session-status', function (Request $request, Response $response, array $args) {
  $status = getSessionStatus($request->getQueryParam('session_id'));
  return $response->withJson($status);
});

function handle_checkout_session($session) {
  markSessionPaid($session->id);
}

$app->run();
