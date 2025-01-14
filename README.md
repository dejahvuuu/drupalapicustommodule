# Safetti Custom Module for Drupal

This project provides a custom Drupal module to manage secure access to protected resources using JSON Web Tokens (JWT). It includes CRUD endpoints for custom data and implements Access and Refresh Token flows for authentication.

---

## **Features**

- JWT-based authentication (Access and Refresh Tokens).
- Secure endpoints for CRUD operations on custom data.
- Token validation using `lcobucci/jwt` library.
- Implements best practices for controller and service separation in Drupal.

---

## **Requirements**

- Docker and Docker Compose.
- Composer.
- PHP 8.1.
- Postman or any API testing tool.
- Drupal 9.5.

---

## **Installation**

### **1. Clone the Repository**

```bash
git clone <repository-url>
cd <project-folder>
```
```bash
composer install
```
### **2. Setup Docker**

Run the Docker containers:

```bash
docker compose up --build 
```

### **3. Install Drupal**

Access `http://localhost:8080` in your browser and complete the Drupal installation using the database credentials defined in `docker-compose.yml`.

### **5. Install Dependencies**

If the local vendor for some reason is not reflected in the docker container
```bash
docker exec -it <drupalcontainername> bash
composer install
```

Enter the Docker container and install the required JWT library:

```bash
docker exec -it <drupalcontainername> bash
composer require lcobucci/jwt
```

### **6. Enable the Module**

Within the container, enable the custom module:

```bash
vendor/bin/drush en safetti_custom -y
```

Clear the Drupal cache:

```bash
vendor/bin/drush cr
```

---

## **Endpoints**

### **1. Generate Tokens**

- **Endpoint:** `/api/v1/safetti-custom/token`
- **Method:** POST
- **Description:** Generates an Access Token and a Refresh Token.

#### Request Body:

```json
{
  "sub": "user_id_123",
  "role": "admin"
}
```

#### Response:

```json
{
  "access_token": "<ACCESS_TOKEN>",
  "refresh_token": "<REFRESH_TOKEN>"
}
```

---

### **2. Refresh Tokens**

- **Endpoint:** `/api/v1/safetti-custom/refresh`
- **Method:** POST
- **Description:** Uses a Refresh Token to generate a new Access Token.

#### Headers:

```
Authorization: Bearer <REFRESH_TOKEN>
```

#### Response:

```json
{
  "access_token": "<NEW_ACCESS_TOKEN>",
  "refresh_token": "<NEW_REFRESH_TOKEN>"
}
```

---

### **3. Get Data**

- **Endpoint:** `/api/v1/safetti-custom`
- **Method:** GET
- **Description:** Retrieves all custom data.

#### Headers:

```
Authorization: Bearer <ACCESS_TOKEN>
```

---

### **4. Insert Data**

- **Endpoint:** `/api/v1/safetti-custom`
- **Method:** POST
- **Description:** Inserts new custom data.

#### Headers:

```
Authorization: Bearer <ACCESS_TOKEN>
```

#### Request Body:

```json
{
  "nid": 1,
  "scenario_id": 100
}
```

---

### **5. Update Data**

- **Endpoint:** `/api/v1/safetti-custom/{id}`
- **Method:** PUT
- **Description:** Updates custom data for the specified ID.

#### Headers:

```
Authorization: Bearer <ACCESS_TOKEN>
```

#### Request Body:

```json
{
  "scenario_id": 200
}
```

---

### **6. Delete Data**

- **Endpoint:** `/api/v1/safetti-custom/{id}`
- **Method:** DELETE
- **Description:** Deletes custom data for the specified ID.

#### Headers:

```
Authorization: Bearer <ACCESS_TOKEN>
```






