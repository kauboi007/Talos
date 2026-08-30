# Talos
Defense analysis system

# Harish
- reorganized project structure
- setup laravel
- dockerized project

# Basic Instructions:
- Start the system via: `docker compose up`
- If you're running the command for the first time in your PC (overall), run this command in cmd to
    initialize the schema: `docker exec -i talos-neo4j-1 cypher-shell -u neo4j -p talossys < .\db\neo4j_schema.cql`
- Or whenever you do clear the docker volumes associated with docker compose.
