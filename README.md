
This is a RESTful API build on PHP framework `Phalcon 3.4.0`. 

It simulates stock management of an online store. 
A store manager can *add*, *search for*, *update* and *delete* products data and avatars using this API.

## Setup and Usage
1. After setting up MySQL server and Apache server, clone this repo to Apache server's `/htdocs` directory. 

2. Create a new database on MySQL server called, for example, `myproducts` and adjust the DI setting part of `index.php` according to what you have created.
```
[
  'host' => 'localhost',
  'username' => 'root',
  'password' => '',
  'dbname' => 'myproducts'
]
```
3. Create a new table on the database and name it `products`. Sample SQL query can be found in `materials/myproducts.sql`.
4. Start to use this API by sending HTTP request using `curl`.

## API Overview

|Action                                             |HTTP Method  |Endpoint URL             |Sample `curl` Command  |Notice   |
| ------------------------------------------------- | ----------- | ----------------------- | --------------------- | --------- |
|登録 <br /> (add new products)                      |POST         |/api/products            |curl -i -X POST -F "name=iphone8" -F "detail=smartphones designed and marketed by Apple Inc." -F "price=74000" -F "image=@/Users/shiiyan/Downloads/iphone8-spgray-select-2018_AV1.png" http://localhost/myRestApi/api/products| File is transferred using format `multipart/form-data`. <br />Binary files are required.|
|検索 <br />(retrieve all products)                  |GET          |/api/products            |curl -i -X GET http://localhost/myRestApi/api/products||
|検索 <br />(search for products with their name "i")|GET          |/api/products/search/{name}     |curl -i -X GET http://localhost/myRestApi/api/products/search/i|One may need to change permission for folder `/uploads` in order to download images.|
|検索 <br />(search for product with its id)         |GET          |/api/products/search/{id:[0-9]+}|curl -i -X GET http://localhost/myRestApi/api/products/search/1|`id` should be an integer|
|変更 <br />(update product data based on its id)    |PUT          |/api/products/{id:[0-9]+}|curl -i -X PUT -d '{"name":"iphone 8"}' http://localhost/myRestApi/api/products/1||
|変更 <br />(update product image based on its id)   |POST         |/api/products/{id:[0-9]+}|curl -i -X POST -F "image=@/Users/shiiyan/Downloads/iphone8-gallery0-2018.jpeg" http://localhost/myRestApi/api/products/1||
|削除 <br />(delete product based on its id)         |DELETE       |/api/products/{id:[0-9]+}|curl -i -X DELETE http://localhost/myRestApi/api/products/1||

Other `curl` commands and sample images could be found in `materials/shell-command.txt`.

