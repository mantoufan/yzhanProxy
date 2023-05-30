#!/bin/bash

if [ -z "$1" ]
then
OUTPUT_NAME="yzhanproxy"
else
OUTPUT_NAME=$1
fi

echo "Building for Windows ..."
export GOOS=windows
export GOARCH=amd64
go build -o build/$OUTPUT_NAME-windows-amd64.exe main.go

echo "Building for Linux ..."
export GOOS=linux
export GOARCH=amd64
go build -o build/$OUTPUT_NAME-linux-amd64 main.go

echo "Building for macOS ..."
export GOOS=darwin
export GOARCH=amd64
go build -o build/$OUTPUT_NAME-darwin-amd64 main.go

echo "Build finished."