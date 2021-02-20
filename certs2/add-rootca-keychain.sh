#!/usr/bin/env bash
sudo security add-trusted-cert -d -r trustRoot -k "/Library/Keychains/System.keychain" RequestCASelfSigned.pem