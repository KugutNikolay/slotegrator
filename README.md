## A. Документация API
### Получения списка продуктов
Request example
<pre>
GET /products?category=category-1&sort=name
Host: api.market.com
Authorization: Bearer 2YotnFZFEjr1zCsicMWpAA
</pre>

Response example
<pre>[
   {
     "id": 1,
     "name": "Example product 1",
     "description": "Example product 1 description",
     "image_url": "https://cdn.market.com/images/products/product_1.png",
     "image_urls": [
       "https://cdn.market.com/images/products/product_1.png"
       "https://s3.amazonaws.com/bucket/product_1_1.png",
       "https://s3.amazonaws.com/bucket/product_1_2.png"
     ],
     "category": "category-1",
     "is_favorite": true
   },
   {
        "id": 2,
        "name": "Example product 2",
        "description": "Example product 2 description",
        "image_url": "https://cdn.market.com/images/products/product_2.png",
        "image_urls": [
          "https://cdn.market.com/images/products/product_2.png"
          "https://s3.amazonaws.com/bucket/product_2_1.png",
          "https://s3.amazonaws.com/bucket/product_2_2.png"
        ],
        "category": "category-1",
        "is_favorite": false
      },
   ...
 ]
</pre>
Если пользователь не авторизирован флаг `is_favorite` не передается в ответе.

### Получение списка избранных товаров.
(Только для авторизированных пользователей)\
Поддерживает те же параметры фильтрации и сортировки, что и основной `\products`

Request example
<pre>
GET /favorites?category=category-1&sort=name
Host: api.market.com
Authorization: Bearer 2YotnFZFEjr1zCsicMWpAA
</pre>

Response example
<pre>[
   {
     "id": 1,
     "name": "Example product 1",
     "description": "Example product 1 description",
     "image_url": "https://cdn.market.com/images/products/product_1.png",
     "images": [
       "https://cdn.market.com/images/products/product_1.png",
       "https://s3.amazonaws.com/bucket/product_1_1.png"
     ],
     "category": "category-1",
     "is_favorite": true
   },
   ...
 ]
</pre>

### Добавление товара в избранные.
(Только для авторизированных пользователей)

Request example
<pre>
POST /favorites
Host: api.market.com
Authorization: Bearer 2YotnFZFEjr1zCsicMWpAA
</pre>

Response example
<pre>
{ 
    "status": "added"
}
</pre>

### Удаление товара из избранных.
(Только для авторизированных пользователей)

Request example
<pre>
DELETE /favorites
Host: api.market.com
Authorization: Bearer 2YotnFZFEjr1zCsicMWpAA
Content-Type: application/json
{
  "product_id": 1
}
</pre>
Response example
<pre>
{ 
    "status": "removed"
}
</pre>

**Ошибки**

| Код | Описание                            |
| --- | ----------------------------------- |
| 401 | Пользователь не авторизован         |
| 404 | Товар не найден                     |
| 422 | Ошибка валидации параметров запроса |
| 500 | Внутренняя ошибка сервера           |

## B. Реализация
1. Обновление Product для работы с несколькими изображениями
<pre>

    namespace Market;

    /**
    * Represents a single product record stored in DB.
    */
    class Product
    {
        /*...*/
        /**
        * @var FileStorageRepository
        */
        private FileStorageRepository $storage;
        
        /**
        * @var AwsStorageInterface
        */
        private ?AwsS3\Client\AwsStorageInterface $awsStorage;
        
        /**
        * @var string
        */
        private string $imageFileName;
        
        /**
        * @var array
        */
        private array $awsFileNames;

        /**
        * @param FileStorageRepository $fileStorageRepository
        */
        public function __construct(FileStorageRepository $fileStorageRepository, ?AwsS3\Client\AwsStorageInterface $awsStorage)
        {
            $this->storage = $fileStorageRepository;
            $this->awsStorage = $awsStorage;
        }

        /*...*/
        
        /**
        * Returns product image URL.
        *
        * @return string|null 
        */
        public function getImageUrl(): ?string
        {
            if ($this->storage->fileExists($this->imageFileName) !== true) {
                return null;
            }
        
            return $this->storage->getUrl($this->imageFileName);
        }
        
        /**
        * Returns product images URL.
        *
        * @return array 
        */
        public function getImageUrls(): array
        {
            $urls = [$this->getImageUrl()];
            if ($this->awsStorage && $this->awsStorage->isAuthorized()) {
                foreach ($this->awsFileNames as $fileName) {
                    try {
                        $urls[] = (string) $this->awsStorage->getUrl($fileName);
                    } catch (\Exception $e) {
                        
                    }
                }
            }
            return $urls;
        }

        /**
        * Returns whether image was successfully updated or not.
        *
        * @return bool 
        */
        public function updateImage(): bool
        {
            /*...*/
            
            try {        
        
            if ($this->storage->fileExists($this->imageFileName) !== true) {
                $this->storage->deleteFile($this->imageFileName);
            }
        
                $this->storage->saveFile($this->imageFileName); 
            } catch (\Exception $exception) {
                /*...*/
            
                return false;
            
            }
        
            /*...*/
            
            return true;
        
        }
        
        /*...*/

}
</pre>

## Composer: Обновление зависимости

1. Сделать fork библиотеки
2. Создать ветку для правок
3. Внести изменения в код библиотеки
4. Закоммитить и запушить изменения 
5. Создать Pull Request. После проверки лидом слить в main/master
6. Подключение через repositories и VCS в composer.json основного проекта
7. Установить/обновить зависимость
8. Протестировать проект 
9. Закоммитить изменения

## Структуры корзины заказов
 [https://github.com/KugutNikolay/slotegrator/commit/b3cfc6dc4103d85c69f6410348c61e838320e9f3](https://github.com/KugutNikolay/slotegrator/commit/b3cfc6dc4103d85c69f6410348c61e838320e9f3)

## Репозиторий билетов Laravel

#### 1. Интерфейс репозитория
<pre>
    namespace App\Repositories;
    
    interface TicketRepositoryInterface
    {
        public function load($ticketId);
        public function save($ticket);
        public function update($ticket);
        public function delete($ticket);
    }
</pre>

#### 2. Реализация репозитория через БД
<pre>
    namespace App\Repositories;
    
    use App\Models\Ticket;
    
    class DatabaseTicketRepository implements TicketRepositoryInterface
    {
        public function load($ticketId)
        {
            return Ticket::find($ticketId);
        }
    
        public function save($ticket)
        {
            return $ticket->save();
        }
    
        public function update($ticket)
        {
            return $ticket->update();
        }
    
        public function delete($ticket)
        {
            return $ticket->delete();
        }
    }
</pre>

#### 3. Реализация репозитория через API
<pre>
    namespace App\Repositories;
     
     use App\Services\ApiClient;
     
     class ApiTicketRepository implements TicketRepositoryInterface
     {
         protected $client;
     
         public function __construct(ApiClient $client)
         {
             $this->client = $client;
         }
     
         public function load($ticketId)
         {
             return $this->client->get("/tickets/{$ticketId}");
         }
     
         public function save($ticket)
         {
             return $this->client->post('/tickets', $ticket);
         }
     
         public function update($ticket)
         {
             return $this->client->put("/tickets/{$ticket['id']}", $ticket);
         }
     
         public function delete($ticket)
         {
             return $this->client->delete("/tickets/{$ticket['id']}");
         }
     }
</pre>

#### 4. API-клиент
<pre>
    namespace App\Services;
     
     use Illuminate\Support\Facades\Http;
     
     class ApiClient
     {
         public function get($url) {}
     
         public function post($url, $data) {}
     
         public function put($url, $data) {}
     
         public function delete($url) {}
     }
</pre>

## SQL: Оценки студентов

#### A. Выборка данных
<pre>
    SELECT 
        CASE 
            WHEN g.grade >= 8 THEN s.name
            ELSE 'low'
        END AS name,
        g.grade,
        s.marks
    FROM 
        students s
    JOIN 
        grade g ON s.marks BETWEEN g.min_mark AND g.max_mark
    ORDER BY 
        g.grade DESC,
        CASE 
            WHEN g.grade >= 8 THEN s.name
            ELSE NULL
        END ASC,
        CASE 
            WHEN g.grade < 8 THEN s.marks
            ELSE NULL
        END ASC;
</pre>

#### B. Модификация DDL 
<pre>
    create table grade (
        grade int not null primary key,
        min_mark int not null,
        max_mark int not null
    );
    
    create table students (
        id bigint not null primary key,
        name varchar(100) not null,
        marks int not null,
        grade int not null,
        foreign key (grade) references grade(grade)
    );
    
    create index idx_students_grade on students(grade);
</pre>

## Docker: Модификация конфигурации сервисов `docker-compose`
Чтобы изменить конфигурацию создаю новы файл `docker-compose.dev.yml`
<pre>
     version: '3'     
     services:
       php:
         image: php:8.1-fpm
         container_name: app-php
         volumes:
           - ./:/var/www
         networks:
           - app-network
       db:
         image: mysql:8
         container_name: app-db
         environment:
           MYSQL_ROOT_PASSWORD: root
           MYSQL_DATABASE: app
           MYSQL_USER: user
           MYSQL_PASSWORD: password
         ports:
           - "3306:3306"
         volumes:
           - ./etc/infrastructure/mysql/my.cnf:/etc/mysql/my.cnf
           - ./etc/database/base.sql:/docker-entrypoint-initdb.d/base.sql
         networks:
           - app-network
       nginx:
         volumes:
           - ./:/var/www
           - ./nginx/default.conf:/etc/nginx/conf.d/default.conf
         networks:
           - app-network
         ports:
           - "8090:80"
           - "443:443"
     networks:
       app-network:
         driver: bridge
</pre>
