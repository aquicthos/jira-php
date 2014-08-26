# Current setup

* Copy jira-api.dist.yml to lib/jira-api.yml and configure values.


# Outstanding tasks

* [ ] Authentication configuration.
* [ ] Devise method for getting and saving user API key or use OAuth token.
* [ ] Abstract appending rest/api/2 to base URL.
* [ ] Fix tests


# Services to add

* createProject
  https://docs.atlassian.com/rpc-jira-plugin/latest/com/atlassian/jira/rpc/soap/JiraSoapService.html#createProject%28java.lang.String,%20java.lang.String,%20java.lang.String,%20java.lang.String,%20java.lang.String,%20java.lang.String,%20com.atlassian.jira.rpc.soap.beans.RemotePermissionScheme,%20com.atlassian.jira.rpc.soap.beans.RemoteScheme,%20com.atlassian.jira.rpc.soap.beans.RemoteScheme%29


# Misc. notes

JIRA REST API: https://docs.atlassian.com/jira/REST/6.3/

Guzzle service descriptions: https://guzzle3.readthedocs.org/webservice-client/guzzle-service-descriptions.html

Need to implement a class that uses discovery to call out to the various JSON-
defined methods. Some methods are not available via REST, e.g. create project.
So, this class will use auto-discovered methods for the Guzzle-provided services
and then traditional methods that implement SoapService for the unsupported
service calls.

Example: https://github.com/cthos/volcano-sdk-php/blob/master/src/Service.php
Example: https://gist.github.com/ziadoz/3126866