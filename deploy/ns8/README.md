# ns8-phonehome
Configuration and files to generate a NethServer 8 compliant module.

- [ns8-phonehome](#ns8-phonehome)
  - [Install](#install)
  - [Configure](#configure)
  - [Uninstall](#uninstall)
  - [Testing](#testing)
  -
## Install

Instantiate the module with:
```bash
add-module ghcr.io/tbaile/phonehome:latest
```

The output of the command will return the instance name.
Output example:
```
{"module_id": "phonehome1", "image_name": "phonehome", "image_url": "ghcr.io/tbaile/phonehome:latest"}
```

## Configure

Let's assume that the phonehome instance is named `phonehome1`.

Launch `configure-module`, by setting the following parameters:
- `hostname`: Hostname where phonehome will be reachable from, for example `phonehome.nethserver.org`;
- `geoip_token`: Valid token from MadMax, for more info [see the documentation](https://github.com/Tbaile/phonehome#geoip2);
- `http_to_https`: Sets up Traefik to redirect all the requests from http to https, can be `true` or `false`;
- `lets_encrypt`: Boolean value that tells traefik to generate the certificates for the `hostname`.

Example:
```bash
api-cli run module/phonehome2/configure-module --data '{ "hostname": "phonehome.nethserver.org", "geoip_token": "XXXXXXXXXXX", "http_to_https": false, "lets_encrypt": false }
```

The above command will:
- start and configure the phonehome instance;
- start all the services, might take a while for the application to get online.

To check if the application is online, simply visit the `hostname` given during the configuration.

## Uninstall

To uninstall the instance:
```bash
remove-module --no-preserve phonehome1
```

## Testing

Test the module using the `test-module.sh` script:
```bash
./test-module.sh <NODE_ADDR> ghcr.io/tbaile/phonehome:latest
```

The tests are made using [Robot Framework](https://robotframework.org/)
