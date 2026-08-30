@echo off

setlocal

echo Starting Docker Compose services...
echo.

docker compose up -d

if errorlevel 1 (
    echo.
    echo ERROR: Failed to start Docker Compose services.
    exit /b 1
)

echo.
echo Starting Laravel development environment...
echo.

docker compose exec -it server composer run dev

if errorlevel 1 (
    echo.
    echo ERROR: Development server stopped or failed to start.
    exit /b 1
)

endlocal