
# Running the project
After cloning the project, you need to run the following command to build the Docker images and start the containers:
```bash
docker compose up --build --remove-orphans --force-recreate
```

Open a browser and navigate to `http://localhost:8080/login` to access the application.

The default credentials are:
- **Username**: `root`
- **Password**: `password`

If the authentication fails, an error message will be displayed.
After the authentication, you will be redirected to the 'httlp://localhost:8080/breweries` page.

The breweries page use the token saved in the local storage to make a request to the API and display the list of breweries.The user has the option to update the token used to make the request and check the error message when the token is invalid.

The datatable is paginated and the user can navigate through the pages.The datatable is also searchable and the user can sort the data by clicking on the column headers.

# Stopping the project
```bash
docker compose down
```

# Some considerations about the project
The project don't use the full support provided by Symfony for the user authentication. This to allow a custom authentication process that involve a token generation.

The project required the use of a common JWT authorization process. Symfony provided a complete support for JWT authentication, but this project don't use it, because the focus of the test was to create a custom authentication process.

However, the project use a simple component to manage the JWT token generation and validation. The component is called `lcobucci/jwt` and is a simple library to create and validate JWT tokens. The library is used to generate the token when the user login and to validate the token when the user access the protected routes.

Please open an issue if you have any question about the project or if you want to suggest a new feature.

Thanks for your time and I hope you enjoy the project.