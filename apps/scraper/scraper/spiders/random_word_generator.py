import scrapy
import json
from scrapy.utils.project import get_project_settings
from typing import Dict, Optional, Iterable


settings = get_project_settings()


class RandomWordGenerator(scrapy.Spider):
    name = 'random_word_generator'
    custom_settings = {
        'ITEM_PIPELINES': {
            'scraper.pipelines.CsvFilePipeline': 1,
        },
        'DOWNLOADER_MIDDLEWARES': {
            'scraper.middlewares.RandomUserAgentMiddleware': 1,
        },
    }
    FILENAME = settings['DATA_DIR'] + '/random_word_generator.csv'

    def start_requests(self) -> scrapy.Request:
        yield scrapy.Request(
            'https://randomwordgenerator.com/json/facts.json',
            callback=self.parse,
        )

    def parse(self, response: scrapy.http.Response) -> Iterable[Dict[str, Optional[str]]]:
        for text in json.loads(response.text)['data']:
            yield {'text': text['fact']}
