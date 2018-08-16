<?php

use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;
use Phalcon\Http\Response;
use Phalcon\Http\Request;


// New Loader() to autoload model
$loader = new Loader();

$loader->registerNamespaces(
	[
		'Store' =>__DIR__.'/models/'
	]
);

$loader->register();

// setup di and database service
$di = new FactoryDefault();

$di->set(
	'db',
	function () {
		return new PdoMysql(
			[
				'host' => 'localhost',
				'username' => 'root',
				'password' => '',
				'dbname' => 'myproducts'
			]
		);
	}
);

// Create and bind the DI to the application
$app = new Micro($di);

// Adds a new product
$app->post(
	'/api/products',
	function () use ($app) {
		$product = $app->request->getPost();

		if ($app->request->hasFiles()) {
			$file = $app->request->getUploadedFiles()[0];
			$file_name = $file->getName();
			$file->moveTo('uploads/'.$file_name);
		} else {
			return json_encode(['status' => 'NO-FILE']);
		}

		$phql = 'INSERT INTO Store\Products (name, detail, price, image_url) VALUES (:name:, :detail:, :price:, :image_url:)';

		$status = $app->modelsManager->executeQuery(
			$phql,
			[
				'name' => $product['name'],
				'detail' => $product['detail'],
				'price' => floatval($product['price']),
				'image_url' => '/uploads/'.$file_name
			]
		);

		// Create a response
		$response = new Response();

		// Check if the insertion was successful
		if ($status->success() === true) {
			// Change the HTTP status
			$response->setStatusCode(201, 'Created');

			$product['id'] = $status->getModel()->id;
			$product['image_url'] = $status->getModel()->image_url;
			
			$response->setJsonContent(
				[
					'status' => 'OK',
					'data' => $product

				], JSON_UNESCAPED_SLASHES
			);
		} else {
			// Change the HTTP status
			$response->setStatusCode(409, 'Conflict');

			// Send errors to the client
			$errors = [];

			foreach($status->getMessages() as $message) {
				$errors[] = $message->getMessage();
			}

			$response->setJsonContent(
				[
					'status' => 'ERROR',
					'messages' => $errors
				]
			);
		}

		return $response;
	}
);

// Retrieves all products
$app->get(
	'/api/products',
	function () use ($app) {
		$phql = 'SELECT * FROM Store\Products ORDER BY id';

		$products = $app->modelsManager->executeQuery($phql);

		echo json_encode($products, JSON_UNESCAPED_SLASHES);
	}
);

// Searches for products with their name
$app->get(
	'/api/products/search/{name}',
	function ($name) use ($app) {
		$phql = 'SELECT * FROM Store\Products WHERE name LIKE :name: ORDER BY id';

		$products = $app->modelsManager->executeQuery(
			$phql,
			[
				'name' => '%'.$name.'%'
			]
		);
		if ($products->valid() === false) {
			echo json_encode(['status' => 'NOT-FOUND']);
		} else {
			echo json_encode($products, JSON_UNESCAPED_SLASHES);
		}
		
	}
);

// Searches for product with its id
$app->get(
	'/api/products/search/{id:[0-9]+}',
	function ($id) use ($app) {
		$phql = 'SELECT * FROM Store\Products WHERE id = :id:';

		$product = $app->modelsManager->executeQuery(
			$phql,
			[
				'id' => $id
			]
		)->getFirst();
		if ($product === false) {
			echo json_encode(['status' => 'NOT-FOUND']);
		} else {
			echo json_encode($product, JSON_UNESCAPED_SLASHES);
		}
		
	}
);

// Updates products data based on id
$app->put(
	'/api/products/{id:[0-9]+}',
	function ($id) use ($app) {
		$product_origin = $app->modelsManager->executeQuery(
			'SELECT * FROM Store\Products WHERE id = :id:',
			['id' => $id]
		)->getFirst();
		$product_new = $app->request->getJsonRawBody();
		// echo gettype($product_new);

		$phql = 'UPDATE Store\Products SET name = :name:, detail = :detail:, price = :price: WHERE id = :id:';

		$status = $app->modelsManager->executeQuery(
			$phql,
			[
				'id' => $id,
				'name' => property_exists($product_new, 'name') ? $product_new->name : $product_origin->name,
				'detail' => property_exists($product_new, 'detail') ? $product_new->detail : $product_origin->detail,
				'price' => property_exists($product_new, 'price') ? floatval($product_new->price) : $product_origin->price,
			]
		);

		// Create a response
		$response = new Response();

		// Check if the insertion was successful
		if ($status->success() === true) {
			$response->setJsonContent(
				[
					'status' => 'OK',
				]
			);
		} else {
			// Change the HTTP status
			$response->setStatusCode(409, 'Conflict');

			// Send errors to the client
			$errors = [];

			foreach($status->getMessages() as $message) {
				$errors[] = $message->getMessage();
			}

			$response->setJsonContent(
				[
					'status' => 'ERROR',
					'messages' => $errors
				]
			);
		}

		return $response;
	}
);

// Updates product image based on its id by POST method
$app->post(
	'/api/products/{id:[0-9]+}',
	function ($id) use ($app) {
		if ($app->request->hasFiles()) {
			$file = $app->request->getUploadedFiles()[0];
			$file_name = $file->getName();
			$file->moveTo('uploads/'.$file_name);

			$phql = 'UPDATE Store\Products SET image_url = :image_url: WHERE id = :id:';

			$status = $app->modelsManager->executeQuery(
				$phql,
				[	
					'id' => $id,
					'image_url' => '/uploads/'.$file_name
				]
			);

			// Create a response
			$response = new Response();

			// Check if the insertion was successful
			if ($status->success() === true) {
				$response->setJsonContent(
					[
						'status' => 'OK',
					]
				);
			} else {
				// Change the HTTP status
				$response->setStatusCode(409, 'Conflict');

				// Send errors to the client
				$errors = [];

				foreach($status->getMessages() as $message) {
					$errors[] = $message->getMessage();
				}

				$response->setJsonContent(
					[
						'status' => 'ERROR',
						'messages' => $errors
					]
				);
			}

			return $response;

		} else {
			return json_encode(['status' => 'NO-FILE']);
		}
	}
);


// Deletes products based on id
$app->delete(
	'/api/products/{id:[0-9]+}',
	function ($id) use ($app) {
		$phql = 'DELETE FROM Store\Products WHERE id = :id:';

			$status = $app->modelsManager->executeQuery(
				$phql,
				[	
					'id' => $id,
				]
			);

			// Create a response
			$response = new Response();

			// Check if the insertion was successful
			if ($status->success() === true) {
				$response->setJsonContent(
					[
						'status' => 'OK',
					]
				);
			} else {
				// Change the HTTP status
				$response->setStatusCode(409, 'Conflict');

				// Send errors to the client
				$errors = [];

				foreach($status->getMessages() as $message) {
					$errors[] = $message->getMessage();
				}

				$response->setJsonContent(
					[
						'status' => 'ERROR',
						'messages' => $errors
					]
				);
			}

			return $response;
	}
);


$app->handle();














