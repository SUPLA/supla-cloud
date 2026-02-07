import ChannelFunctionAction from '@/common/enums/channel-function-action.js';
import ChannelFunction from '@/common/enums/channel-function.js';

export const useDirectLinkExamples = () => {
  return {
    snippets,
    exampleParams,
    exampleModeLabels,
  };
};

const exampleParams = {
  [ChannelFunctionAction.REVEAL_PARTIALLY]: (subject) => {
    const params = [
      {
        name: 'percentage',
        example: 60,
        description: 'Percentage of opening (e.g. {example})', // i18n
      },
    ];
    if ([ChannelFunction.CONTROLLINGTHEFACADEBLIND, ChannelFunction.VERTICAL_BLIND].includes(subject?.functionId)) {
      params.push({
        name: 'tilt',
        example: 10,
        description: 'Set tilt angle in percent', // i18n
      });
    }
    return params;
  },
  [ChannelFunctionAction.SHUT_PARTIALLY]: (subject) => {
    const params = [
      {
        name: 'percentage',
        example: 60,
        description: 'Percentage of closing (e.g. {example})', // i18n
      },
    ];
    if ([ChannelFunction.CONTROLLINGTHEFACADEBLIND, ChannelFunction.VERTICAL_BLIND].includes(subject?.functionId)) {
      params.push({
        name: 'tilt',
        example: 10,
        description: 'Set tilt angle in percent', // i18n
      });
    }
    return params;
  },
  [ChannelFunctionAction.COPY]: () => {
    return [
      {
        name: 'sourceChannelId',
        example: 123,
        description: 'Identifier of a channel to copy state from (e.g. {example})', // i18n
      },
    ];
  },
  [ChannelFunctionAction.HVAC_SET_TEMPERATURE]: () => {
    return [
      {
        name: 'temperature',
        example: 21,
        description: 'Temperature to set in Celsius degrees (e.g. {example})', // i18n
      },
    ];
  },
  [ChannelFunctionAction.HVAC_SET_TEMPERATURES]: () => {
    return [
      {
        name: 'temperatureHeat',
        example: 21,
        description: 'Heating temperature to set in Celsius degrees (e.g. {example})', // i18n
      },
      {
        name: 'temperatureCool',
        example: 27,
        description: 'Cooling temperature to set in Celsius degrees (e.g. {example})', // i18n
      },
    ];
  },
  [ChannelFunctionAction.SET_RGBW_PARAMETERS]: (subject) => {
    const params = [];
    if ([ChannelFunction.RGBLIGHTING, ChannelFunction.DIMMERANDRGBLIGHTING, ChannelFunction.DIMMER_CCT_AND_RGB].includes(subject?.functionId)) {
      params.push({
        name: 'color',
        example: '0xFF6600',
        description: 'Color value in HEX format (e.g. {example})', // i18n
      });
      params.push({
        name: 'color_brightness',
        example: 60,
        description: 'Color brightness in percent (e.g. {example})', // i18n
      });
    }
    if (
      [ChannelFunction.DIMMER, ChannelFunction.DIMMERANDRGBLIGHTING, ChannelFunction.DIMMER_CCT, ChannelFunction.DIMMER_CCT_AND_RGB].includes(
        subject?.functionId
      )
    ) {
      params.push({
        name: 'brightness',
        example: 60,
        description: 'Brightness value in percent format (e.g. {example})', // i18n
      });
    }
    if ([ChannelFunction.DIMMER_CCT, ChannelFunction.DIMMER_CCT_AND_RGB].includes(subject?.functionId)) {
      params.push({
        name: 'white_temperature',
        example: 60,
        description: 'White temperature in percent, 0 - warmest, 100 - coolest (e.g. {example})', // i18n
      });
    }
    return params;
  },
};

const exampleModeLabels = {
  link: 'Link only', // i18n
  curl: 'cURL', // i18n
  python: 'Python', // i18n
  php: 'PHP', // i18n
  java: 'Java', // i18n
  js: 'JavaScript', // i18n
  cpp: 'C++', // i18n
};

const snippets = {
  php: (url, body) => {
    const bodyJson = JSON.stringify(body);
    return `<?php

$url = '${url}';
$payload = '${bodyJson}'; // or other - see below

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

$response = curl_exec($ch);
curl_close($ch);

echo $response;`;
  },
  python: (url, body) => {
    const bodyJson = JSON.stringify(body, null, 2);
    return `import requests

url = "${url}"
payload = ${bodyJson}

response = requests.patch(url, json=payload)
print(response.text)`;
  },
  js: (url, body) => {
    const bodyJson = JSON.stringify(body);
    return `fetch("${url}", {
  method: "PATCH",
  headers: {"Content-Type": "application/json"},
  body: JSON.stringify(${bodyJson}),
})
  .then(r => r.json())
  .then(console.log);`;
  },
  cpp: (url, body) => {
    const bodyJson = JSON.stringify(body).replace(/"/g, '\\"');
    return `#include <curl/curl.h>

int main() {
    CURL* curl = curl_easy_init();
    if (!curl) return 1;

    const char* url = "${url}";
    const char* payload = "${bodyJson}";

    curl_easy_setopt(curl, CURLOPT_URL, url);
    curl_easy_setopt(curl, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_easy_setopt(curl, CURLOPT_POSTFIELDS, payload);

    struct curl_slist* headers = nullptr;
    headers = curl_slist_append(headers, "Content-Type: application/json");
    curl_easy_setopt(curl, CURLOPT_HTTPHEADER, headers);

    curl_easy_perform(curl);

    curl_slist_free_all(headers);
    curl_easy_cleanup(curl);
}`;
  },
  java: (url, body) => {
    const bodyJson = JSON.stringify(body).replace(/"/g, '\\"');
    return `import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;

public class Main {
    public static void main(String[] args) throws Exception {
        String url = "${url}";
        String payload = "${bodyJson}";

        HttpClient client = HttpClient.newHttpClient();

        HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(url))
            .method("PATCH", HttpRequest.BodyPublishers.ofString(payload))
            .header("Content-Type", "application/json")
            .build();

        HttpResponse<String> response =
            client.send(request, HttpResponse.BodyHandlers.ofString());

        System.out.println(response.body());
    }
}
`;
  },
  curl: (url, body) => {
    const bodyJson = JSON.stringify(body);
    return `curl -X PATCH "${url}" \\
  -H "Content-Type: application/json" \\
  -d '${bodyJson}'`;
  },
};
