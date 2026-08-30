@echo off
setlocal

echo [1/4] Starting Docker Compose services...
docker compose up -d

if errorlevel 1 (
    echo.
    echo ERROR: Failed to start Docker Compose.
    exit /b 1
)

echo Docker Compose services started.
echo.

echo [2/4] Waiting for Neo4j...

:wait_for_neo4j
docker compose exec -T neo4j cypher-shell -u neo4j -p talossys "RETURN 1;" >nul 2>&1

if errorlevel 1 (
    timeout /t 2 /nobreak >nul
    goto wait_for_neo4j
)

echo Neo4j is ready.
echo.

echo [3/4] Initializing Neo4j database...

docker compose exec -T neo4j cypher-shell -u neo4j -p talossys < ".\db\neo4j_schema.cql"

if errorlevel 1 (
    echo.
    echo ERROR: Failed to initialize Neo4j database.
    exit /b 1
)

echo Neo4j database initialized.
echo.

echo [4/4] Running Composer setup...
docker compose exec -T server composer setup

if errorlevel 1 (
    echo.
    echo ERROR: Composer setup failed.
    exit /b 1
)

echo.
echo Setup completed successfully.
echo To start the development server, run: start.bat
echo.

endlocal