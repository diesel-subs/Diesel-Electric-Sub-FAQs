#!/bin/bash

# Docker helper script for MkDocs project

case "$1" in
    "dev"|"serve")
        echo "Starting MkDocs development server in Docker..."
        docker-compose up --build
        ;;
    "build")
        echo "Building static site in Docker..."
        docker-compose --profile build run --rm mkdocs-build
        ;;
    "stop")
        echo "Stopping Docker containers..."
        docker-compose down
        ;;
    "clean")
        echo "Cleaning up Docker resources..."
        docker-compose down --volumes --remove-orphans
        docker system prune -f
        ;;
    "shell")
        echo "Opening shell in MkDocs container..."
        docker-compose run --rm mkdocs bash
        ;;
    *)
        echo "Usage: $0 {dev|serve|build|stop|clean|shell}"
        echo ""
        echo "Commands:"
        echo "  dev/serve  - Start development server (http://localhost:8000)"
        echo "  build      - Build static site"
        echo "  stop       - Stop running containers"
        echo "  clean      - Clean up Docker resources"
        echo "  shell      - Open shell in container"
        exit 1
        ;;
esac