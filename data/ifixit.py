"""
iFixit Repair Guide Scraper - COMPLETE WORKING VERSION WITH ALL MODELS
Scrapes repair guides from iFixit website and converts to training dataset format
Output: CSV with columns: description, fault
"""

import requests
from bs4 import BeautifulSoup
import csv
import time
from urllib.parse import urljoin
import logging
import re

# Setup logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

# iFixit base URL
IFIXIT_BASE = "https://www.ifixit.com"

# ALL MODELS for each brand - Comprehensive list
DEVICES = [
    # ========== iPHONE (ALL MODELS 11-15) ==========
    # iPhone 15 Series
    {"name": "iPhone 15", "url": "https://www.ifixit.com/Device/iPhone_15"},
    {"name": "iPhone 15 Plus", "url": "https://www.ifixit.com/Device/iPhone_15_Plus"},
    {"name": "iPhone 15 Pro", "url": "https://www.ifixit.com/Device/iPhone_15_Pro"},
    {"name": "iPhone 15 Pro Max", "url": "https://www.ifixit.com/Device/iPhone_15_Pro_Max"},
    
    # iPhone 14 Series
    {"name": "iPhone 14", "url": "https://www.ifixit.com/Device/iPhone_14"},
    {"name": "iPhone 14 Plus", "url": "https://www.ifixit.com/Device/iPhone_14_Plus"},
    {"name": "iPhone 14 Pro", "url": "https://www.ifixit.com/Device/iPhone_14_Pro"},
    {"name": "iPhone 14 Pro Max", "url": "https://www.ifixit.com/Device/iPhone_14_Pro_Max"},
    
    # iPhone 13 Series
    {"name": "iPhone 13", "url": "https://www.ifixit.com/Device/iPhone_13"},
    {"name": "iPhone 13 Mini", "url": "https://www.ifixit.com/Device/iPhone_13_Mini"},
    {"name": "iPhone 13 Pro", "url": "https://www.ifixit.com/Device/iPhone_13_Pro"},
    {"name": "iPhone 13 Pro Max", "url": "https://www.ifixit.com/Device/iPhone_13_Pro_Max"},
    
    # iPhone 12 Series
    {"name": "iPhone 12", "url": "https://www.ifixit.com/Device/iPhone_12"},
    {"name": "iPhone 12 Mini", "url": "https://www.ifixit.com/Device/iPhone_12_Mini"},
    {"name": "iPhone 12 Pro", "url": "https://www.ifixit.com/Device/iPhone_12_Pro"},
    {"name": "iPhone 12 Pro Max", "url": "https://www.ifixit.com/Device/iPhone_12_Pro_Max"},
    
    # iPhone 11 Series
    {"name": "iPhone 11", "url": "https://www.ifixit.com/Device/iPhone_11"},
    {"name": "iPhone 11 Pro", "url": "https://www.ifixit.com/Device/iPhone_11_Pro"},
    {"name": "iPhone 11 Pro Max", "url": "https://www.ifixit.com/Device/iPhone_11_Pro_Max"},
    
    # iPhone SE Series
    {"name": "iPhone SE (2022)", "url": "https://www.ifixit.com/Device/iPhone_SE_2022"},
    {"name": "iPhone SE (2020)", "url": "https://www.ifixit.com/Device/iPhone_SE_2020"},
    
    # ========== SAMSUNG (ALL MODELS 2021-LATEST) ==========
    # Galaxy S24 Series (Latest)
    {"name": "Samsung Galaxy S24 Ultra", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_S24_Ultra"},
    {"name": "Samsung Galaxy S24+", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_S24_Plus"},
    {"name": "Samsung Galaxy S24", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_S24"},
    
    # Galaxy S23 Series
    {"name": "Samsung Galaxy S23 Ultra", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_S23_Ultra"},
    {"name": "Samsung Galaxy S23+", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_S23_Plus"},
    {"name": "Samsung Galaxy S23", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_S23"},
    {"name": "Samsung Galaxy S23 FE", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_S23_FE"},
    
    # Galaxy S22 Series
    {"name": "Samsung Galaxy S22 Ultra", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_S22_Ultra"},
    {"name": "Samsung Galaxy S22+", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_S22_Plus"},
    {"name": "Samsung Galaxy S22", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_S22"},
    
    # Galaxy S21 Series
    {"name": "Samsung Galaxy S21 Ultra", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_S21_Ultra"},
    {"name": "Samsung Galaxy S21+", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_S21_Plus"},
    {"name": "Samsung Galaxy S21", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_S21"},
    {"name": "Samsung Galaxy S21 FE", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_S21_FE"},
    
    # Galaxy Z Series (Fold/Flip)
    {"name": "Samsung Galaxy Z Fold 5", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_Z_Fold_5"},
    {"name": "Samsung Galaxy Z Flip 5", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_Z_Flip_5"},
    {"name": "Samsung Galaxy Z Fold 4", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_Z_Fold_4"},
    {"name": "Samsung Galaxy Z Flip 4", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_Z_Flip_4"},
    {"name": "Samsung Galaxy Z Fold 3", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_Z_Fold_3"},
    {"name": "Samsung Galaxy Z Flip 3", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_Z_Flip_3"},
    
    # Galaxy A Series (Mid-range)
    {"name": "Samsung Galaxy A54", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_A54"},
    {"name": "Samsung Galaxy A34", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_A34"},
    {"name": "Samsung Galaxy A14", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_A14"},
    {"name": "Samsung Galaxy A73", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_A73"},
    {"name": "Samsung Galaxy A53", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_A53"},
    {"name": "Samsung Galaxy A33", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_A33"},
    {"name": "Samsung Galaxy A13", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_A13"},
    {"name": "Samsung Galaxy A52", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_A52"},
    {"name": "Samsung Galaxy A72", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_A72"},
    
    # Galaxy M Series
    {"name": "Samsung Galaxy M54", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_M54"},
    {"name": "Samsung Galaxy M34", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_M34"},
    {"name": "Samsung Galaxy M14", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_M14"},
    {"name": "Samsung Galaxy M53", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_M53"},
    {"name": "Samsung Galaxy M33", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_M33"},
    
    # Galaxy Note Series (Discontinued but still used)
    {"name": "Samsung Galaxy Note 20 Ultra", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_Note_20_Ultra"},
    {"name": "Samsung Galaxy Note 20", "url": "https://www.ifixit.com/Device/Samsung_Galaxy_Note_20"},
    
    # ========== HUAWEI (2022-LATEST) ==========
    # P Series
    {"name": "Huawei P60 Pro", "url": "https://www.ifixit.com/Device/Huawei_P60_Pro"},
    {"name": "Huawei P60", "url": "https://www.ifixit.com/Device/Huawei_P60"},
    {"name": "Huawei P50 Pro", "url": "https://www.ifixit.com/Device/Huawei_P50_Pro"},
    {"name": "Huawei P50", "url": "https://www.ifixit.com/Device/Huawei_P50"},
    
    # Mate Series
    {"name": "Huawei Mate 50 Pro", "url": "https://www.ifixit.com/Device/Huawei_Mate_50_Pro"},
    {"name": "Huawei Mate 50", "url": "https://www.ifixit.com/Device/Huawei_Mate_50"},
    {"name": "Huawei Mate 40 Pro", "url": "https://www.ifixit.com/Device/Huawei_Mate_40_Pro"},
    
    # Nova Series
    {"name": "Huawei Nova 11", "url": "https://www.ifixit.com/Device/Huawei_Nova_11"},
    {"name": "Huawei Nova 11 Pro", "url": "https://www.ifixit.com/Device/Huawei_Nova_11_Pro"},
    {"name": "Huawei Nova 10", "url": "https://www.ifixit.com/Device/Huawei_Nova_10"},
    {"name": "Huawei Nova 10 Pro", "url": "https://www.ifixit.com/Device/Huawei_Nova_10_Pro"},
    {"name": "Huawei Nova 9", "url": "https://www.ifixit.com/Device/Huawei_Nova_9"},
    
    # Y Series
    {"name": "Huawei Y90", "url": "https://www.ifixit.com/Device/Huawei_Y90"},
    {"name": "Huawei Y70", "url": "https://www.ifixit.com/Device/Huawei_Y70"},
    
    # ========== OPPO (2020-LATEST) ==========
    # Find Series (Flagship)
    {"name": "Oppo Find X5 Pro", "url": "https://www.ifixit.com/Device/Oppo_Find_X5_Pro"},
    {"name": "Oppo Find X5", "url": "https://www.ifixit.com/Device/Oppo_Find_X5"},
    {"name": "Oppo Find X3 Pro", "url": "https://www.ifixit.com/Device/Oppo_Find_X3_Pro"},
    {"name": "Oppo Find X3", "url": "https://www.ifixit.com/Device/Oppo_Find_X3"},
    {"name": "Oppo Find X2 Pro", "url": "https://www.ifixit.com/Device/Oppo_Find_X2_Pro"},
    
    # Reno Series
    {"name": "Oppo Reno 10 Pro+", "url": "https://www.ifixit.com/Device/Oppo_Reno_10_Pro_Plus"},
    {"name": "Oppo Reno 10 Pro", "url": "https://www.ifixit.com/Device/Oppo_Reno_10_Pro"},
    {"name": "Oppo Reno 10", "url": "https://www.ifixit.com/Device/Oppo_Reno_10"},
    {"name": "Oppo Reno 9 Pro+", "url": "https://www.ifixit.com/Device/Oppo_Reno_9_Pro_Plus"},
    {"name": "Oppo Reno 9 Pro", "url": "https://www.ifixit.com/Device/Oppo_Reno_9_Pro"},
    {"name": "Oppo Reno 9", "url": "https://www.ifixit.com/Device/Oppo_Reno_9"},
    {"name": "Oppo Reno 8 Pro", "url": "https://www.ifixit.com/Device/Oppo_Reno_8_Pro"},
    {"name": "Oppo Reno 8", "url": "https://www.ifixit.com/Device/Oppo_Reno_8"},
    {"name": "Oppo Reno 7 Pro", "url": "https://www.ifixit.com/Device/Oppo_Reno_7_Pro"},
    {"name": "Oppo Reno 7", "url": "https://www.ifixit.com/Device/Oppo_Reno_7"},
    {"name": "Oppo Reno 6 Pro", "url": "https://www.ifixit.com/Device/Oppo_Reno_6_Pro"},
    {"name": "Oppo Reno 6", "url": "https://www.ifixit.com/Device/Oppo_Reno_6"},
    
    # A Series (Budget)
    {"name": "Oppo A78", "url": "https://www.ifixit.com/Device/Oppo_A78"},
    {"name": "Oppo A77", "url": "https://www.ifixit.com/Device/Oppo_A77"},
    {"name": "Oppo A76", "url": "https://www.ifixit.com/Device/Oppo_A76"},
    {"name": "Oppo A57", "url": "https://www.ifixit.com/Device/Oppo_A57"},
    {"name": "Oppo A54", "url": "https://www.ifixit.com/Device/Oppo_A54"},
    {"name": "Oppo A53", "url": "https://www.ifixit.com/Device/Oppo_A53"},
    
    # F Series
    {"name": "Oppo F21 Pro", "url": "https://www.ifixit.com/Device/Oppo_F21_Pro"},
    {"name": "Oppo F19 Pro", "url": "https://www.ifixit.com/Device/Oppo_F19_Pro"},
    
    # ========== VIVO (2022-LATEST) ==========
    # X Series (Flagship)
    {"name": "Vivo X90 Pro", "url": "https://www.ifixit.com/Device/Vivo_X90_Pro"},
    {"name": "Vivo X90", "url": "https://www.ifixit.com/Device/Vivo_X90"},
    {"name": "Vivo X80 Pro", "url": "https://www.ifixit.com/Device/Vivo_X80_Pro"},
    {"name": "Vivo X80", "url": "https://www.ifixit.com/Device/Vivo_X80"},
    {"name": "Vivo X70 Pro+", "url": "https://www.ifixit.com/Device/Vivo_X70_Pro_Plus"},
    {"name": "Vivo X70 Pro", "url": "https://www.ifixit.com/Device/Vivo_X70_Pro"},
    
    # V Series
    {"name": "Vivo V27 Pro", "url": "https://www.ifixit.com/Device/Vivo_V27_Pro"},
    {"name": "Vivo V27", "url": "https://www.ifixit.com/Device/Vivo_V27"},
    {"name": "Vivo V25 Pro", "url": "https://www.ifixit.com/Device/Vivo_V25_Pro"},
    {"name": "Vivo V25", "url": "https://www.ifixit.com/Device/Vivo_V25"},
    {"name": "Vivo V23 Pro", "url": "https://www.ifixit.com/Device/Vivo_V23_Pro"},
    {"name": "Vivo V23", "url": "https://www.ifixit.com/Device/Vivo_V23"},
    
    # Y Series
    {"name": "Vivo Y100", "url": "https://www.ifixit.com/Device/Vivo_Y100"},
    {"name": "Vivo Y56", "url": "https://www.ifixit.com/Device/Vivo_Y56"},
    {"name": "Vivo Y36", "url": "https://www.ifixit.com/Device/Vivo_Y36"},
    {"name": "Vivo Y22", "url": "https://www.ifixit.com/Device/Vivo_Y22"},
    {"name": "Vivo Y21", "url": "https://www.ifixit.com/Device/Vivo_Y21"},
    
    # T Series
    {"name": "Vivo T2", "url": "https://www.ifixit.com/Device/Vivo_T2"},
    {"name": "Vivo T1", "url": "https://www.ifixit.com/Device/Vivo_T1"},
    
    # ========== INFINIX (2020-LATEST) ==========
    # GT Series (Gaming)
    {"name": "Infinix GT 10 Pro", "url": "https://www.ifixit.com/Device/Infinix_GT_10_Pro"},
    {"name": "Infinix GT 20 Pro", "url": "https://www.ifixit.com/Device/Infinix_GT_20_Pro"},
    
    # Note Series
    {"name": "Infinix Note 30", "url": "https://www.ifixit.com/Device/Infinix_Note_30"},
    {"name": "Infinix Note 30 Pro", "url": "https://www.ifixit.com/Device/Infinix_Note_30_Pro"},
    {"name": "Infinix Note 12", "url": "https://www.ifixit.com/Device/Infinix_Note_12"},
    {"name": "Infinix Note 12 Pro", "url": "https://www.ifixit.com/Device/Infinix_Note_12_Pro"},
    {"name": "Infinix Note 11", "url": "https://www.ifixit.com/Device/Infinix_Note_11"},
    {"name": "Infinix Note 10", "url": "https://www.ifixit.com/Device/Infinix_Note_10"},
    
    # Zero Series
    {"name": "Infinix Zero 30", "url": "https://www.ifixit.com/Device/Infinix_Zero_30"},
    {"name": "Infinix Zero 20", "url": "https://www.ifixit.com/Device/Infinix_Zero_20"},
    {"name": "Infinix Zero X Pro", "url": "https://www.ifixit.com/Device/Infinix_Zero_X_Pro"},
    {"name": "Infinix Zero 5G", "url": "https://www.ifixit.com/Device/Infinix_Zero_5G"},
    
    # Hot Series
    {"name": "Infinix Hot 30", "url": "https://www.ifixit.com/Device/Infinix_Hot_30"},
    {"name": "Infinix Hot 30i", "url": "https://www.ifixit.com/Device/Infinix_Hot_30i"},
    {"name": "Infinix Hot 20", "url": "https://www.ifixit.com/Device/Infinix_Hot_20"},
    {"name": "Infinix Hot 20i", "url": "https://www.ifixit.com/Device/Infinix_Hot_20i"},
    {"name": "Infinix Hot 12", "url": "https://www.ifixit.com/Device/Infinix_Hot_12"},
    {"name": "Infinix Hot 11", "url": "https://www.ifixit.com/Device/Infinix_Hot_11"},
    {"name": "Infinix Hot 10", "url": "https://www.ifixit.com/Device/Infinix_Hot_10"},
    
    # Smart Series
    {"name": "Infinix Smart 7", "url": "https://www.ifixit.com/Device/Infinix_Smart_7"},
    {"name": "Infinix Smart 6", "url": "https://www.ifixit.com/Device/Infinix_Smart_6"},
    {"name": "Infinix Smart 5", "url": "https://www.ifixit.com/Device/Infinix_Smart_5"},
    
    # ========== HONOR (2021-LATEST) ==========
    # Magic Series
    {"name": "Honor Magic 5 Pro", "url": "https://www.ifixit.com/Device/Honor_Magic_5_Pro"},
    {"name": "Honor Magic 5", "url": "https://www.ifixit.com/Device/Honor_Magic_5"},
    {"name": "Honor Magic 4 Pro", "url": "https://www.ifixit.com/Device/Honor_Magic_4_Pro"},
    {"name": "Honor Magic 4", "url": "https://www.ifixit.com/Device/Honor_Magic_4"},
    {"name": "Honor Magic 3 Pro", "url": "https://www.ifixit.com/Device/Honor_Magic_3_Pro"},
    
    # Honor Series
    {"name": "Honor 90", "url": "https://www.ifixit.com/Device/Honor_90"},
    {"name": "Honor 90 Lite", "url": "https://www.ifixit.com/Device/Honor_90_Lite"},
    {"name": "Honor 80", "url": "https://www.ifixit.com/Device/Honor_80"},
    {"name": "Honor 80 Pro", "url": "https://www.ifixit.com/Device/Honor_80_Pro"},
    {"name": "Honor 70", "url": "https://www.ifixit.com/Device/Honor_70"},
    {"name": "Honor 60", "url": "https://www.ifixit.com/Device/Honor_60"},
    {"name": "Honor 50", "url": "https://www.ifixit.com/Device/Honor_50"},
    
    # X Series
    {"name": "Honor X9a", "url": "https://www.ifixit.com/Device/Honor_X9a"},
    {"name": "Honor X9", "url": "https://www.ifixit.com/Device/Honor_X9"},
    {"name": "Honor X8", "url": "https://www.ifixit.com/Device/Honor_X8"},
    {"name": "Honor X7", "url": "https://www.ifixit.com/Device/Honor_X7"},
    {"name": "Honor X6", "url": "https://www.ifixit.com/Device/Honor_X6"},
    
    # ========== REALME (2020-LATEST) ==========
    # GT Series (Performance)
    {"name": "Realme GT 5", "url": "https://www.ifixit.com/Device/Realme_GT_5"},
    {"name": "Realme GT 3", "url": "https://www.ifixit.com/Device/Realme_GT_3"},
    {"name": "Realme GT 2 Pro", "url": "https://www.ifixit.com/Device/Realme_GT_2_Pro"},
    {"name": "Realme GT 2", "url": "https://www.ifixit.com/Device/Realme_GT_2"},
    {"name": "Realme GT", "url": "https://www.ifixit.com/Device/Realme_GT"},
    {"name": "Realme GT Neo 5", "url": "https://www.ifixit.com/Device/Realme_GT_Neo_5"},
    {"name": "Realme GT Neo 3", "url": "https://www.ifixit.com/Device/Realme_GT_Neo_3"},
    
    # Number Series
    {"name": "Realme 11 Pro+", "url": "https://www.ifixit.com/Device/Realme_11_Pro_Plus"},
    {"name": "Realme 11 Pro", "url": "https://www.ifixit.com/Device/Realme_11_Pro"},
    {"name": "Realme 11", "url": "https://www.ifixit.com/Device/Realme_11"},
    {"name": "Realme 10 Pro+", "url": "https://www.ifixit.com/Device/Realme_10_Pro_Plus"},
    {"name": "Realme 10 Pro", "url": "https://www.ifixit.com/Device/Realme_10_Pro"},
    {"name": "Realme 10", "url": "https://www.ifixit.com/Device/Realme_10"},
    {"name": "Realme 9 Pro+", "url": "https://www.ifixit.com/Device/Realme_9_Pro_Plus"},
    {"name": "Realme 9 Pro", "url": "https://www.ifixit.com/Device/Realme_9_Pro"},
    {"name": "Realme 8 Pro", "url": "https://www.ifixit.com/Device/Realme_8_Pro"},
    {"name": "Realme 8", "url": "https://www.ifixit.com/Device/Realme_8"},
    
    # C Series (Budget)
    {"name": "Realme C55", "url": "https://www.ifixit.com/Device/Realme_C55"},
    {"name": "Realme C53", "url": "https://www.ifixit.com/Device/Realme_C53"},
    {"name": "Realme C35", "url": "https://www.ifixit.com/Device/Realme_C35"},
    {"name": "Realme C33", "url": "https://www.ifixit.com/Device/Realme_C33"},
    {"name": "Realme C25", "url": "https://www.ifixit.com/Device/Realme_C25"},
    {"name": "Realme C21", "url": "https://www.ifixit.com/Device/Realme_C21"},
    
    # Narzo Series
    {"name": "Realme Narzo 60", "url": "https://www.ifixit.com/Device/Realme_Narzo_60"},
    {"name": "Realme Narzo 60 Pro", "url": "https://www.ifixit.com/Device/Realme_Narzo_60_Pro"},
    {"name": "Realme Narzo 50", "url": "https://www.ifixit.com/Device/Realme_Narzo_50"},
    {"name": "Realme Narzo 30", "url": "https://www.ifixit.com/Device/Realme_Narzo_30"},
    
    # ========== TECNO (2022-LATEST) ==========
    # Phantom Series (Flagship)
    {"name": "Tecno Phantom V Fold", "url": "https://www.ifixit.com/Device/Tecno_Phantom_V_Fold"},
    {"name": "Tecno Phantom V Flip", "url": "https://www.ifixit.com/Device/Tecno_Phantom_V_Flip"},
    {"name": "Tecno Phantom X2 Pro", "url": "https://www.ifixit.com/Device/Tecno_Phantom_X2_Pro"},
    {"name": "Tecno Phantom X2", "url": "https://www.ifixit.com/Device/Tecno_Phantom_X2"},
    
    # Camon Series (Camera focused)
    {"name": "Tecno Camon 20 Pro", "url": "https://www.ifixit.com/Device/Tecno_Camon_20_Pro"},
    {"name": "Tecno Camon 20", "url": "https://www.ifixit.com/Device/Tecno_Camon_20"},
    {"name": "Tecno Camon 19 Pro", "url": "https://www.ifixit.com/Device/Tecno_Camon_19_Pro"},
    {"name": "Tecno Camon 19", "url": "https://www.ifixit.com/Device/Tecno_Camon_19"},
    {"name": "Tecno Camon 18", "url": "https://www.ifixit.com/Device/Tecno_Camon_18"},
    
    # Spark Series
    {"name": "Tecno Spark 10 Pro", "url": "https://www.ifixit.com/Device/Tecno_Spark_10_Pro"},
    {"name": "Tecno Spark 10", "url": "https://www.ifixit.com/Device/Tecno_Spark_10"},
    {"name": "Tecno Spark 9 Pro", "url": "https://www.ifixit.com/Device/Tecno_Spark_9_Pro"},
    {"name": "Tecno Spark 9", "url": "https://www.ifixit.com/Device/Tecno_Spark_9"},
    {"name": "Tecno Spark 8", "url": "https://www.ifixit.com/Device/Tecno_Spark_8"},
    
    # Pova Series (Gaming)
    {"name": "Tecno Pova 5", "url": "https://www.ifixit.com/Device/Tecno_Pova_5"},
    {"name": "Tecno Pova 5 Pro", "url": "https://www.ifixit.com/Device/Tecno_Pova_5_Pro"},
    {"name": "Tecno Pova 4", "url": "https://www.ifixit.com/Device/Tecno_Pova_4"},
    {"name": "Tecno Pova 3", "url": "https://www.ifixit.com/Device/Tecno_Pova_3"},
    
    # ========== POCO (2022-LATEST) ==========
    # F Series (Flagship)
    {"name": "Poco F5 Pro", "url": "https://www.ifixit.com/Device/Poco_F5_Pro"},
    {"name": "Poco F5", "url": "https://www.ifixit.com/Device/Poco_F5"},
    {"name": "Poco F4 GT", "url": "https://www.ifixit.com/Device/Poco_F4_GT"},
    {"name": "Poco F4", "url": "https://www.ifixit.com/Device/Poco_F4"},
    {"name": "Poco F3", "url": "https://www.ifixit.com/Device/Poco_F3"},
    
    # X Series
    {"name": "Poco X5 Pro", "url": "https://www.ifixit.com/Device/Poco_X5_Pro"},
    {"name": "Poco X5", "url": "https://www.ifixit.com/Device/Poco_X5"},
    {"name": "Poco X4 Pro", "url": "https://www.ifixit.com/Device/Poco_X4_Pro"},
    {"name": "Poco X4 GT", "url": "https://www.ifixit.com/Device/Poco_X4_GT"},
    {"name": "Poco X3 Pro", "url": "https://www.ifixit.com/Device/Poco_X3_Pro"},
    
    # M Series
    {"name": "Poco M5", "url": "https://www.ifixit.com/Device/Poco_M5"},
    {"name": "Poco M5s", "url": "https://www.ifixit.com/Device/Poco_M5s"},
    {"name": "Poco M4 Pro", "url": "https://www.ifixit.com/Device/Poco_M4_Pro"},
    {"name": "Poco M4", "url": "https://www.ifixit.com/Device/Poco_M4"},
    {"name": "Poco M3", "url": "https://www.ifixit.com/Device/Poco_M3"},
    {"name": "Poco M3 Pro", "url": "https://www.ifixit.com/Device/Poco_M3_Pro"},
    
    # C Series (Budget)
    {"name": "Poco C55", "url": "https://www.ifixit.com/Device/Poco_C55"},
    {"name": "Poco C51", "url": "https://www.ifixit.com/Device/Poco_C51"},
    {"name": "Poco C40", "url": "https://www.ifixit.com/Device/Poco_C40"},
    {"name": "Poco C31", "url": "https://www.ifixit.com/Device/Poco_C31"},
    
    # ========== CHERRY MOBILE (2022-LATEST) ==========
    # Aqua Series
    {"name": "Cherry Mobile Aqua S10 Pro", "url": "https://www.ifixit.com/Device/Cherry_Mobile_Aqua_S10_Pro"},
    {"name": "Cherry Mobile Aqua S10", "url": "https://www.ifixit.com/Device/Cherry_Mobile_Aqua_S10"},
    {"name": "Cherry Mobile Aqua S9", "url": "https://www.ifixit.com/Device/Cherry_Mobile_Aqua_S9"},
    {"name": "Cherry Mobile Aqua S8", "url": "https://www.ifixit.com/Device/Cherry_Mobile_Aqua_S8"},
    
    # Flare Series
    {"name": "Cherry Mobile Flare S8", "url": "https://www.ifixit.com/Device/Cherry_Mobile_Flare_S8"},
    {"name": "Cherry Mobile Flare S7", "url": "https://www.ifixit.com/Device/Cherry_Mobile_Flare_S7"},
    {"name": "Cherry Mobile Flare S6", "url": "https://www.ifixit.com/Device/Cherry_Mobile_Flare_S6"},
    
    # Omega Series
    {"name": "Cherry Mobile Omega HD", "url": "https://www.ifixit.com/Device/Cherry_Mobile_Omega_HD"},
    {"name": "Cherry Mobile Omega Lite", "url": "https://www.ifixit.com/Device/Cherry_Mobile_Omega_Lite"},
    
    # Cosmos Series
    {"name": "Cherry Mobile Cosmos X", "url": "https://www.ifixit.com/Device/Cherry_Mobile_Cosmos_X"},
    {"name": "Cherry Mobile Cosmos Z", "url": "https://www.ifixit.com/Device/Cherry_Mobile_Cosmos_Z"},
]

print(f"Total devices configured: {len(DEVICES)}")
print(f"Brands covered: iPhone, Samsung, Huawei, Oppo, Vivo, Infinix, Honor, Realme, Tecno, Poco, Cherry Mobile")

# Diagnosis mapping from repair type to fault label
DIAGNOSIS_MAP = {
    "Battery Replacement": "Battery Issue",
    "battery": "Battery Issue",
    "Charging Port": "Charging Port Issue",
    "charging port": "Charging Port Issue",
    "Charger IC": "Charging IC Issue",
    "charging": "Charging IC Issue",
    "Display": "Display Issue",
    "Screen Replacement": "Display Issue",
    "screen": "Display Issue",
    "LCD": "Display IC Issue",
    "OLED": "Display IC Issue",
    "Touch": "Touch Controller Issue",
    "Digitizer": "Touch Controller Issue",
    "touch screen": "Touch Controller Issue",
    "Speaker": "Speaker Issue",
    "Microphone": "Microphone Issue",
    "Antenna": "Antenna Issue",
    "Motherboard": "Mainboard Issue",
    "Logic Board": "Mainboard Issue",
    "main board": "Mainboard Issue",
    "Power": "Power IC Issue",
    "thermal": "Power IC Issue",
    "SIM": "SIM IC Issue",
    "Network": "Baseband Issue",
    "WiFi": "Antenna Issue",
    "Bluetooth": "Baseband Issue",
    "Water": "Water Damage - Inspect All Components",
    "liquid": "Water Damage - Inspect All Components",
    "Software": "Software/OS Issue",
    "Frozen": "Software/OS Issue",
    "Boot": "Software/OS Issue",
    "firmware": "Software/OS Issue",
    "camera": "Camera Issue",
    "back glass": "Back Glass Issue",
    "rear glass": "Back Glass Issue",
    "face id": "Face ID Issue",
    "fingerprint": "Fingerprint Sensor Issue",
    "vibrator": "Vibration Motor Issue",
    "vibration": "Vibration Motor Issue",
    "headphone": "Headphone Jack Issue",
    "audio jack": "Headphone Jack Issue",
    "usb port": "Charging Port Issue",
    "power button": "Button Issue",
    "volume button": "Button Issue",
    "side key": "Button Issue",
    "overheating": "Overheating Issue",
    "heat": "Overheating Issue",
}

class iFixitScraper:
    def __init__(self):
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language': 'en-US,en;q=0.5',
            'Accept-Encoding': 'gzip, deflate, br',
            'DNT': '1',
            'Connection': 'keep-alive',
            'Upgrade-Insecure-Requests': '1'
        })
        self.data = []
    
    def get_device_guides(self, device):
        """Get repair guides for a specific device from its device page"""
        logger.info(f"Fetching guides for {device['name']}...")
        guides = []
        
        try:
            # First try to get the device page
            response = self.session.get(device['url'], timeout=15)
            response.raise_for_status()
            
            soup = BeautifulSoup(response.content, 'html.parser')
            
            # Look for guides section
            guide_links = []
            
            # Find all guide links
            for link in soup.find_all('a', href=True):
                href = link['href']
                # Check if it's a repair guide URL
                if '/Guide/' in href or '/Teardown/' in href or '/Repair/' in href:
                    full_url = urljoin(IFIXIT_BASE, href)
                    title = link.get_text(strip=True)
                    if title and len(title) > 5:  # Avoid empty or very short titles
                        if full_url not in [g['url'] for g in guide_links]:  # Avoid duplicates
                            guide_links.append({
                                'title': title,
                                'url': full_url,
                                'device': device['name']
                            })
            
            # Look for guides in specific sections
            guides_section = soup.find('section', {'id': 'guides'}) or soup.find('div', {'class': 'guides'}) or soup.find('div', {'class': 'guide-list'})
            if guides_section:
                for link in guides_section.find_all('a', href=True):
                    if '/Guide/' in link['href'] or '/Teardown/' in link['href']:
                        full_url = urljoin(IFIXIT_BASE, link['href'])
                        title = link.get_text(strip=True)
                        if title and len(title) > 5:
                            if full_url not in [g['url'] for g in guide_links]:
                                guide_links.append({
                                    'title': title,
                                    'url': full_url,
                                    'device': device['name']
                                })
            
            # Limit to first 15 guides to avoid too many requests
            guides = guide_links[:15]
            
            if guides:
                logger.info(f"Found {len(guides)} guides for {device['name']}")
            else:
                logger.warning(f"No guides found for {device['name']}, trying search...")
                # Fallback to search
                guides = self.search_guides(device['name'])
            
        except Exception as e:
            logger.error(f"Error getting guides for {device['name']}: {str(e)}")
            # Try search as fallback
            guides = self.search_guides(device['name'])
        
        return guides
    
    def search_guides(self, device_name):
        """Fallback: Search for guides using iFixit search page"""
        guides = []
        try:
            search_url = f"{IFIXIT_BASE}/Search"
            params = {'query': device_name}
            
            response = self.session.get(search_url, params=params, timeout=10)
            response.raise_for_status()
            
            soup = BeautifulSoup(response.content, 'html.parser')
            
            # Find guide links in search results
            for link in soup.find_all('a', href=True):
                href = link['href']
                if '/Guide/' in href or '/Teardown/' in href:
                    full_url = urljoin(IFIXIT_BASE, href)
                    title = link.get_text(strip=True)
                    if title and len(title) > 5:
                        if full_url not in [g['url'] for g in guides]:
                            guides.append({
                                'title': title,
                                'url': full_url,
                                'device': device_name
                            })
            
            guides = guides[:10]  # Limit to 10
            logger.info(f"Search found {len(guides)} guides for {device_name}")
            
        except Exception as e:
            logger.error(f"Error searching for {device_name}: {str(e)}")
        
        return guides
    
    def extract_guide_info(self, guide, device_name):
        """Extract information from a single repair guide page"""
        try:
            logger.info(f"Processing: {guide['title'][:50]}...")
            
            response = self.session.get(guide['url'], timeout=15)
            response.raise_for_status()
            
            soup = BeautifulSoup(response.content, 'html.parser')
            
            # Extract guide title
            title_elem = soup.find('h1') or soup.find('h2', class_='title') or soup.find('h2', class_='guide-title')
            title_text = title_elem.get_text(strip=True) if title_elem else guide['title']
            
            # Extract difficulty and time if available
            difficulty = ""
            time_estimate = ""
            
            # Look for difficulty badge
            difficulty_elem = soup.find('span', class_='difficulty') or soup.find('span', class_='badge') or soup.find('div', class_='difficulty')
            if difficulty_elem:
                difficulty = difficulty_elem.get_text(strip=True)
            
            # Extract introduction/summary
            intro = ""
            intro_elem = soup.find('div', class_='introduction') or soup.find('div', class_='summary') or soup.find('div', class_='guide-introduction')
            if intro_elem:
                intro = intro_elem.get_text(strip=True)
            else:
                # Try to get meta description
                meta = soup.find('meta', {'name': 'description'})
                if meta:
                    intro = meta.get('content', '')
            
            # Extract steps summary
            steps_text = []
            steps = soup.find_all('div', class_='step') or soup.find_all('li', class_='step') or soup.find_all('div', class_='guide-step')
            
            for step in steps[:3]:  # First 3 steps only
                step_text = step.get_text(strip=True)
                if step_text and len(step_text) > 10:
                    steps_text.append(step_text[:100])  # Truncate long steps
            
            # Extract parts/tools mentioned
            parts_tools = []
            parts_section = soup.find('h2', string=re.compile(r'Parts|Tools|Required', re.I))
            if parts_section:
                next_elem = parts_section.find_next('ul') or parts_section.find_next('div') or parts_section.find_next('ol')
                if next_elem:
                    parts_tools = [item.get_text(strip=True) for item in next_elem.find_all(['li', 'p'])[:5]]
            
            # Extract conclusion/final notes
            conclusion = ""
            conclusion_elem = soup.find('h2', string=re.compile(r'Conclusion|Final', re.I))
            if conclusion_elem:
                next_elem = conclusion_elem.find_next('p') or conclusion_elem.find_next('div')
                if next_elem:
                    conclusion = next_elem.get_text(strip=True)[:200]
            
            # Create comprehensive description
            description_parts = [title_text]
            if difficulty:
                description_parts.append(f"Difficulty: {difficulty}")
            if intro:
                description_parts.append(intro[:200])
            if steps_text:
                description_parts.append("Steps: " + "; ".join(steps_text))
            if parts_tools:
                description_parts.append("Parts: " + ", ".join(parts_tools))
            if conclusion:
                description_parts.append(f"Result: {conclusion}")
            
            full_description = ". ".join(description_parts)
            
            # Clean up description
            full_description = re.sub(r'\s+', ' ', full_description).strip()
            full_description = re.sub(r'\.\.+', '.', full_description)  # Remove multiple periods
            
            # Infer fault from title and content
            fault = self.infer_fault(title_text + " " + intro + " " + " ".join(parts_tools) + " " + " ".join(steps_text))
            
            if fault and len(full_description) > 30:
                return {
                    'description': full_description[:600],  # Limit length
                    'fault': fault,
                    'device': device_name
                }
            else:
                logger.warning(f"Could not determine fault for: {title_text[:50]}")
                return None
            
        except Exception as e:
            logger.warning(f"Error extracting from guide {guide['url']}: {str(e)}")
            return None
    
    def infer_fault(self, text):
        """Infer fault/diagnosis from repair text"""
        text_lower = text.lower()
        
        # Check against diagnosis map
        for keyword, diagnosis in DIAGNOSIS_MAP.items():
            if keyword.lower() in text_lower:
                return diagnosis
        
        # More specific pattern matching
        fault_patterns = [
            (r'battery.*(replace|dead|not charging|drain|swelling|swollen)', "Battery Issue"),
            (r'charging.*(port|not working|loose|broken|damaged)', "Charging Port Issue"),
            (r'(screen|display).*(cracked|broken|black|no image|flickering|lines)', "Display Issue"),
            (r'touch.*(not working|unresponsive|ghost touch|digitizer)', "Touch Controller Issue"),
            (r'(speaker|audio|sound).*(not working|distorted|no sound|quiet)', "Speaker Issue"),
            (r'(mic|microphone).*(not working|no audio|calls)', "Microphone Issue"),
            (r'(wifi|wi-fi|bluetooth).*(not working|connecting|signal)', "Antenna Issue"),
            (r'(water|liquid|moisture).*(damage|corrosion|wet)', "Water Damage - Inspect All Components"),
            (r'(bootloop|stuck|freeze|crash|restart|reboot)', "Software/OS Issue"),
            (r'(power|won\'t turn on|dead|no power|shutdown)', "Power IC Issue"),
            (r'camera.*(not working|blurry|black|crash)', "Camera Issue"),
            (r'back glass.*(broken|cracked|shattered)', "Back Glass Issue"),
            (r'face id.*(not working|recognize)', "Face ID Issue"),
            (r'fingerprint.*(not working|sensor)', "Fingerprint Sensor Issue"),
            (r'vibrat.*(not working|motor)', "Vibration Motor Issue"),
            (r'headphone.*(not working|jack)', "Headphone Jack Issue"),
            (r'overheat.*(hot|temperature|thermal)', "Overheating Issue"),
            (r'sim.*(not detected|card|reader)', "SIM IC Issue"),
            (r'network.*(signal|no service|baseband|cellular)', "Baseband Issue"),
            (r'motherboard.*(dead|faulty|repair|logic board)', "Mainboard Issue"),
        ]
        
        for pattern, diagnosis in fault_patterns:
            if re.search(pattern, text_lower):
                return diagnosis
        
        # If still no match, look for common component names
        if any(word in text_lower for word in ['screen', 'display', 'lcd', 'oled']):
            return "Display Issue"
        elif any(word in text_lower for word in ['battery', 'charge']):
            return "Battery Issue"
        elif any(word in text_lower for word in ['water', 'liquid']):
            return "Water Damage - Inspect All Components"
        elif any(word in text_lower for word in ['camera']):
            return "Camera Issue"
        elif any(word in text_lower for word in ['speaker', 'audio']):
            return "Speaker Issue"
        elif any(word in text_lower for word in ['wifi', 'bluetooth']):
            return "Antenna Issue"
        
        return None
    
    def scrape(self, max_guides_per_device=5):
        """Main scraping function"""
        logger.info(f"Starting iFixit scraper for {len(DEVICES)} devices...")
        logger.info(f"Will scrape up to {max_guides_per_device} guides per device")
        
        total_guides_found = 0
        successful_extractions = 0
        devices_with_guides = 0
        
        for i, device in enumerate(DEVICES, 1):
            logger.info(f"\n[{i}/{len(DEVICES)}] Processing {device['name']}...")
            
            # Get guides for this device
            guides = self.get_device_guides(device)
            
            if guides:
                devices_with_guides += 1
                total_guides_found += len(guides)
                logger.info(f"Processing {min(len(guides), max_guides_per_device)} of {len(guides)} guides for {device['name']}")
                
                # Extract info from each guide
                for guide in guides[:max_guides_per_device]:
                    try:
                        info = self.extract_guide_info(guide, device['name'])
                        if info:
                            self.data.append(info)
                            successful_extractions += 1
                            logger.info(f"✓ Extracted: {info['fault']}")
                        else:
                            logger.debug(f"✗ Failed to extract from: {guide['title'][:50]}...")
                    except Exception as e:
                        logger.debug(f"Error processing guide: {str(e)}")
                    
                    # Be nice to iFixit's servers
                    time.sleep(1.5)  # Delay between guides
            else:
                logger.warning(f"No guides found for {device['name']}")
            
            # Progress indicator
            if i % 10 == 0:
                logger.info(f"Progress: {i}/{len(DEVICES)} devices processed. Found {successful_extractions} samples so far.")
            
            # Delay between devices
            if i < len(DEVICES):
                time.sleep(2)
        
        logger.info(f"\nScraping complete!")
        logger.info(f"Devices with guides: {devices_with_guides}/{len(DEVICES)}")
        logger.info(f"Total guides found: {total_guides_found}")
        logger.info(f"Successfully extracted: {successful_extractions}")
        return self.data
    
    def save_to_csv(self, filename='ifixit_dataset.csv'):
        """Save scraped data to CSV in training format"""
        if not self.data:
            logger.warning("No data to save!")
            return None
        
        try:
            with open(filename, 'w', newline='', encoding='utf-8') as f:
                writer = csv.DictWriter(f, fieldnames=['description', 'fault'])
                writer.writeheader()
                
                # Only include rows with valid description and fault
                # Remove the 'device' field before saving
                valid_rows = []
                for row in self.data:
                    if row.get('description') and row.get('fault'):
                        # Create a new dict with only the fields we want
                        clean_row = {
                            'description': row['description'],
                            'fault': row['fault']
                        }
                        valid_rows.append(clean_row)
                
                writer.writerows(valid_rows)
            
            logger.info(f"✓ Saved {len(valid_rows)} samples to {filename}")
            
            # Show a preview
            logger.info("\nFirst 3 samples:")
            for i, row in enumerate(valid_rows[:3]):
                logger.info(f"\n{i+1}. Fault: {row['fault']}")
                logger.info(f"   Description: {row['description'][:100]}...")
            
            return filename
            
        except Exception as e:
            logger.error(f"Error saving to CSV: {str(e)}")
            return None
    
    def print_summary(self):
        """Print summary of scraped data"""
        logger.info("\n" + "="*60)
        logger.info("SCRAPING SUMMARY")
        logger.info("="*60)
        
        if not self.data:
            logger.info("No data scraped!")
            return
        
        logger.info(f"Total samples: {len(self.data)}")
        
        # Count by fault type
        fault_counts = {}
        device_counts = {}
        
        for row in self.data:
            fault = row.get('fault', 'Unknown')
            device = row.get('device', 'Unknown')
            
            fault_counts[fault] = fault_counts.get(fault, 0) + 1
            device_counts[device] = device_counts.get(device, 0) + 1
        
        logger.info("\nSamples by Diagnosis:")
        for fault, count in sorted(fault_counts.items(), key=lambda x: x[1], reverse=True)[:15]:
            logger.info(f"  {fault}: {count}")
        
        logger.info("\nSamples by Device (top 15):")
        for device, count in sorted(device_counts.items(), key=lambda x: x[1], reverse=True)[:15]:
            logger.info(f"  {device}: {count}")
        
        logger.info("="*60 + "\n")


def main():
    """Main entry point"""
    scraper = iFixitScraper()
    
    logger.info("="*60)
    logger.info("iFIXIT REPAIR GUIDE SCRAPER - COMPLETE VERSION")
    logger.info("="*60)
    logger.info(f"Total devices configured: {len(DEVICES)}")
    logger.info(f"Brands covered: iPhone, Samsung, Huawei, Oppo, Vivo, Infinix, Honor, Realme, Tecno, Poco, Cherry Mobile")
    logger.info("="*60)
    
    # Scrape guides (adjust max_guides_per_device to control runtime)
    # 3-5 is good for testing, 8-10 for production
    data = scraper.scrape(max_guides_per_device=5)
    
    if data:
        # Save to CSV
        output_file = scraper.save_to_csv('ifixit_training_dataset.csv')
        
        # Print summary
        scraper.print_summary()
        
        if output_file:
            logger.info(f"✓ Dataset ready: {output_file}")
            logger.info("You can now use this CSV file for training your ML model!")
    else:
        logger.error("No data was scraped. Please check:")
        logger.error("1. Your internet connection")
        logger.error("2. If iFixit.com is accessible")
        logger.error("3. Try running again with different devices or lower max_guides_per_device")


if __name__ == "__main__":
    main()