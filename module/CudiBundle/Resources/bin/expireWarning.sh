#!/bin/bash

# A very small wrapper around our expire warning script
#

scriptDirectory=$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)
cd "${scriptDirectory}/../../"

php bin/CudiBundle/expireWarning.php -rm