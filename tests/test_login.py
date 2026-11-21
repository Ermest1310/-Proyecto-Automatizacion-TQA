import os
from selenium import webdriver
from pages.login_page import LoginPage

def test_valid_login():
    driver = webdriver.Chrome()
    driver.get("https://the-internet.herokuapp.com/login")
    try:
        login_page = LoginPage(driver)
        login_page.login(os.environ["QA_DEMO_USERNAME"], os.environ["QA_DEMO_PASSWORD"])
        assert "Secure Area" in driver.title
    finally:
        driver.quit()

if __name__ == "__main__":
    test_valid_login()
