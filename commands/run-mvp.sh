#!/bin/bash
# run-mvp.sh

echo "🚀 Iniciando Security Platform MVP..."
echo "======================================"

# 1. Build e start dos containers
echo "📦 Construindo containers..."
docker-compose build

echo "🐳 Iniciando serviços..."
docker-compose up -d

# 2. Aguardar MySQL estar pronto
echo "⏳ Aguardando MySQL..."
sleep 10

# 3. Instalar dependências
echo "📚 Instalando dependências Composer..."
docker exec security-app composer install --no-interaction


# 4. Gerar chave da aplicação
echo "🔑 Gerando chave da aplicação..."
docker exec security-app php artisan key:generate


# 5. Executar migrations
echo "🗄️ Executando migrations..."
docker exec security-app php artisan migrate --force

# 6. Popular banco com planos
echo "📊 Populando banco de dados..."
#docker exec security-app php artisan db:seed --class=PlanSeeder

# 7. Criar usuário de teste
echo "👤 Criando usuário de teste..."
#docker exec security-app php artisan tinker --execute="
#    \$user = new App\Models\User();
#    \$user->name = 'Admin Teste';
#    \$user->email = 'admin@teste.com';
#    \$user->password = bcrypt('password');
#    \$user->save();
#    echo 'Usuário criado: admin@teste.com / password';
#"

# 8. Limpar cache
echo "🧹 Limpando cache..."
docker exec security-app php artisan optimize:clear

echo ""
echo "✅ MVP PRONTO!"
echo "======================================"
echo "📱 Acesse: http://localhost:8000"
echo "📧 Login: admin@teste.com"
echo "🔐 Senha: password"
echo ""
echo "📊 Grafana: http://localhost:3001 (admin/admin)"
echo "📈 Prometheus: http://localhost:9090"
echo ""
echo "🔍 Para testar:"
echo "1. Faça login"
echo "2. Vá para Planos e assine um plano"
echo "3. Vá para Scans e crie um novo scan"
echo "4. Acompanhe o status no Dashboard"
