@echo off

set output_name=yzhanproxy

if "%1" neq "" (
  set output_name=%1
)

echo Building for Windows ...
set GOOS=windows
set GOARCH=amd64
go build -o build\%output_name%-windows-amd64.exe main.go

echo Building for Linux ...
set GOOS=linux
set GOARCH=amd64
go build -o build\%output_name%-linux-amd64 main.go

echo Building for macOS ...
set GOOS=darwin
set GOARCH=amd64
go build -o build\%output_name%-darwin-amd64 main.go

echo Build finished.