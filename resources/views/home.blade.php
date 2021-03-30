
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Pixel</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
    body {
        padding-top: 5rem;
    }
    
    .starter-template {
        padding: 3rem 1.5rem;
        text-align: center;
    }
    .label {
        color:#145222
    }
    </style>
  </head>
  <body>
    <nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
      <span class="navbar-brand">Pixel</span>
    </nav>
    <div class="container">
      <div class="row">
          <div class="col-12-md">
            <h2>1. Usage</h2>
            <p><code>{{$host}}/api/v1/{category}</code></p>
            <h5>Valid Categories</h5>
            <ul>
              @foreach($validCategories as $category)
              <li>{{ $category }}</li>
              @endforeach
            </ul>
            <p>Example: <a href="{{$host}}/api/v1/{{$randomCategory}}" target="_blank">{{$host}}/api/v1/{{$randomCategory}}</a></p>
            <h2>2. Random Image</h2>
            <p>If you need random image:</p>
            <p><a href="{{$host}}/api/v1/" target="_blank">{{$host}}/api/v1/</a></p>
            <h2>3. Limit rate</h2>
            <p>The API endpoint rate limit is 60 requests per minute</p>

            <h3>License</h3>
            <p>
            <small>THE SERVICE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
            IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
            FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
            AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
            LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
            OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
            SOFTWARE.</small>
            </p>
          </div>
      </div>
    </div>
  </body>
</html>
