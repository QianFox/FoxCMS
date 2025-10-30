<?php
namespace app\validate;
use think\Validate;

class BaseValidate extends Validate
{
    protected $allowed_params = ['activepath'];

    /**
     * 验证失败时返回400错误并终止
     */
    protected function handleValidationError()
    {
        require_once('./static/405/405.html');
        exit();
    }

    /**
     * 宽松模式验证允许特定字符和.css/.html后缀
     * @param string $input 原始输入值
     */
    public function validateWhitelistInput($input)
    {
        if (!preg_match('/^[a-zA-Z0-9_.\/-]+(\.css|\.html)?$/', $input)) {
            $this->handleValidationError();
        }
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * 严格模式验证禁止路径遍历符号
     * @param string $input 原始输入值
     */
    public function validateStrictInput($input)
    {
        if (!preg_match('/^(?!.*\.\.)(?!.*\.\/)(?!.*\/\.)[\p{L}\p{N}_,\-\/.=%]*$/u', $input)) {
            $this->handleValidationError();
        }
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * 中等模式验证（适用于非白名单参数）
     * @param string $input 原始输入值
     */
    public function validateMediumInput($input)
    {
        // 处理数组类型输入（支持多维数组）
        if (is_array($input)) {
            return array_map([$this, 'validateMediumInput'], $input);
        }

        // 处理非字符串类型
        if (!is_string($input)) {
            return $input;
        }

        // 解码 HTML 实体
        $decodedInput = html_entity_decode($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
         // ▼▼▼ 新增SQL注入防护 ▼▼▼
    $sqlInjectionPatterns = [
        '/(union[\s]+select)/i',        // UNION SELECT 注入
        '/(select|update|delete|insert)[\s]+.*from/i', // SQL操作语句
        '/\b(?:drop|alter|truncate|rename|create)\b/i', // 数据库结构操作
        '/\b(?:sleep|benchmark)\b\(/i', // 时间盲注函数
        '/(?:0x[a-f0-9]{2,}|[\'";](\s*--|#|\/\*))/i', // 十六进制编码和注释符
        '/\b(?:or|and)\b\s+[\d\w]+\s*=\s*[\d\w]+/i' // 永真条件
    ];

    foreach ($sqlInjectionPatterns as $pattern) {
        if (preg_match($pattern, $decodedInput)) {
            $this->handleValidationError();
        }
    }

        // 过滤掉 <script> 标签，但保留其内部内容
        $filteredInput = preg_replace_callback('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/is', function ($matches) {
            // 移除 <script> 和 </script> 标签，保留内部内容
            return preg_replace('/<\/?script\b[^>]*>/is', '', $matches[0]);
        }, $decodedInput);

        // 过滤掉 javascript: 关键字
        $filteredInput = preg_replace('/javascript:/i', '', $filteredInput);

        // 过滤掉 onXXX= 属性
        $filteredInput = preg_replace('/on\w+=(["\'][^"\']*["\'])/i', '', $filteredInput);

        return $filteredInput;
    }
    /**
     * 验证所有GET参数（白名单参数用宽松模式）
     */
    public function validateGetParams()
    {
        foreach ($_GET as $key => $value) {
            in_array($key, $this->allowed_params)
                ? $this->validateWhitelistInput($value)
                : $this->validateStrictInput($value);
        }
    }

    /**
     * 验证所有Cookie参数（白名单参数用宽松模式）
     */
    public function validateCookieParams()
    {
        foreach ($_COOKIE as $key => $value) {
            in_array($key, $this->allowed_params)
                ? $this->validateWhitelistInput($value)
                : $this->validateStrictInput($value);
        }
    }

    /**
     * 验证POST请求的所有参数（白名单参数用宽松模式）
     */
    public function validatePostData()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        // 通过URL路径判断是否需要跳过验证
        $pathInfo = $_SERVER['PATH_INFO'] ?? '';
        if ($this->shouldSkipValidation($pathInfo)) {
            return;
        }
        // 通过完整URL路径判断
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        if ($this->shouldSkipByUri($requestUri)) {
            return;
        }
        foreach ($_POST as $key => $value) {
            $_POST[$key] = $this->validateMediumInput($value); // 统一验证方式
        }
    }

    /**
     * 根据完整URL特征判断是否跳过验证
     */
    private function shouldSkipByUri($uri)
    {
        // 匹配特征路径（示例URL中的关键部分）
        $patterns = [
            '/\/template_file\/editFile\.html/',
            '/\/template_file\/addFile\.html/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $uri)) {
                return true;
            }
        }
        return false;
    }
}
