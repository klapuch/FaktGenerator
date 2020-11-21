import scrapy
import json
import logging
from scrapy.utils.project import get_project_settings
from typing import Dict, Optional, Iterable


settings = get_project_settings()


class MentalFloss(scrapy.Spider):
    name = 'mental_floss'
    custom_settings = {
        'ITEM_PIPELINES': {
            'scraper.pipelines.CsvFilePipeline': 1,
        },
        'DOWNLOADER_MIDDLEWARES': {
            'scraper.middlewares.RandomUserAgentMiddleware': 1,
        },
    }
    FILENAME = settings['DATA_DIR'] + '/mental-floss.csv'
    NUMBER_OF_ATTEMPTS = 100

    actual = 0

    def start_requests(self) -> scrapy.Request:
        for attempt in range(0, self.NUMBER_OF_ATTEMPTS):
            yield scrapy.Request(
                'https://www.mentalfloss.com/api/facts?limit=40',
                callback=self.parse,
                dont_filter=True,
            )

    def parse(self, response: scrapy.http.Response) -> Iterable[Dict[str, Optional[str]]]:
        self.actual = self.actual + 1
        logging.info('Gathering facts (%d)', self.actual)
        for text in json.loads(response.text):
            yield {'text': text['fact']}
